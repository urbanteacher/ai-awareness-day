#!/usr/bin/env swift
/**
 * Rasterise each lesson deck PDF to one JPEG per slide.
 *
 * The lesson page's "Show on the board" view shows one slide at a time, large,
 * with the PDF viewer one tap away. The PDF viewer can't do the first part, so
 * this writes a picture of every page next to the PDF:
 *
 *   assets/lessons/2027/AiAd27-Classic-Safe.pdf
 *   assets/lessons/2027/slides/AiAd27-Classic-Safe/01.jpg … 15.jpg
 *   assets/lessons/2027/share/AiAd27-Classic-Safe.jpg      (1200x630)
 *
 * single-resource.php looks for that folder from the PDF's own path, so a
 * lesson with no folder simply opens on the PDF viewer instead. Page N is
 * slide N: the steps' "Slide 4" is 04.jpg.
 *
 * The share card is the cover slide fitted to the 1200x630 shape that link
 * previews use (og:image), so a lesson shared on social media shows its own
 * title in the 2027 look rather than the site logo. See aiad_resource_deck_images().
 *
 * macOS only (CoreGraphics), nothing to install. Re-run after any PDF changes:
 *
 *   swift scripts/export-lesson-slides.swift            # every PDF in assets/lessons/
 *   swift scripts/export-lesson-slides.swift path.pdf   # just that one
 */
import Foundation
import CoreGraphics
import ImageIO
import UniformTypeIdentifiers

let width = 1600       // px; sharp on a 1080p projector, ~140 KB a slide
let quality = 0.85
let shareSize = CGSize(width: 1200, height: 630)  // the og:image shape
let shareQuality = 0.9
let progressBar = 7.0 / 900.0  // the strip along a slide's foot, as a fraction of its height

let root = URL(fileURLWithPath: CommandLine.arguments[0]).deletingLastPathComponent().deletingLastPathComponent()
let fm = FileManager.default

var pdfs: [URL] = []
if CommandLine.arguments.count > 1 {
    pdfs = CommandLine.arguments.dropFirst().map { URL(fileURLWithPath: $0) }
} else {
    let lessons = root.appendingPathComponent("assets/lessons")
    if let walk = fm.enumerator(at: lessons, includingPropertiesForKeys: nil) {
        for case let url as URL in walk where url.pathExtension.lowercased() == "pdf" {
            pdfs.append(url)
        }
    }
}
pdfs.sort { $0.path < $1.path }

func writeJPEG(_ image: CGImage, to file: URL, quality: Double) -> Int {
    guard let dest = CGImageDestinationCreateWithURL(file as CFURL, UTType.jpeg.identifier as CFString, 1, nil) else { return 0 }
    CGImageDestinationAddImage(dest, image, [kCGImageDestinationLossyCompressionQuality: quality] as CFDictionary)
    CGImageDestinationFinalize(dest)
    return (try? fm.attributesOfItem(atPath: file.path)[.size] as? Int) ?? 0
}

/// The slide's own background colour, read from its left edge, halfway down.
func edgeColour(of image: CGImage) -> CGColor {
    var pixel = [UInt8](repeating: 0, count: 4)
    let ctx = CGContext(data: &pixel, width: 1, height: 1, bitsPerComponent: 8, bytesPerRow: 4,
                        space: CGColorSpaceCreateDeviceRGB(), bitmapInfo: CGImageAlphaInfo.noneSkipLast.rawValue)!
    ctx.draw(image, in: CGRect(x: -2, y: -(image.height / 2), width: image.width, height: image.height))
    // Same colour space as the bitmaps, or the fill drifts from the slide.
    return CGColor(colorSpace: CGColorSpaceCreateDeviceRGB(),
                   components: [CGFloat(pixel[0]) / 255, CGFloat(pixel[1]) / 255, CGFloat(pixel[2]) / 255, 1])!
}

/// A cover slide fitted to the share-card shape: whole slide in view, its
/// progress bar dropped, the sides filled with its own colour.
func shareCard(from cover: CGImage) -> CGImage? {
    let bodyHeight = Int((Double(cover.height) * (1 - progressBar)).rounded())
    guard let body = cover.cropping(to: CGRect(x: 0, y: 0, width: cover.width, height: bodyHeight)),
          let ctx = CGContext(data: nil, width: Int(shareSize.width), height: Int(shareSize.height),
                              bitsPerComponent: 8, bytesPerRow: 0, space: CGColorSpaceCreateDeviceRGB(),
                              bitmapInfo: CGImageAlphaInfo.noneSkipLast.rawValue) else { return nil }
    ctx.setFillColor(edgeColour(of: cover))
    ctx.fill(CGRect(origin: .zero, size: shareSize))
    ctx.interpolationQuality = .high
    let drawWidth = shareSize.height * CGFloat(body.width) / CGFloat(body.height)
    ctx.draw(body, in: CGRect(x: (shareSize.width - drawWidth) / 2, y: 0, width: drawWidth, height: shareSize.height))
    return ctx.makeImage()
}

var failed = false
for pdf in pdfs {
    guard let doc = CGPDFDocument(pdf as CFURL), doc.numberOfPages > 0 else {
        print("skip  \(pdf.lastPathComponent): not a readable PDF")
        failed = true
        continue
    }
    let out = pdf.deletingLastPathComponent()
        .appendingPathComponent("slides")
        .appendingPathComponent(pdf.deletingPathExtension().lastPathComponent)
    try? fm.removeItem(at: out) // drop stale pages if the deck got shorter
    try fm.createDirectory(at: out, withIntermediateDirectories: true)

    var bytes = 0
    var cover: CGImage?
    for n in 1...doc.numberOfPages {
        guard let page = doc.page(at: n) else { continue }
        let box = page.getBoxRect(.cropBox)
        let rotated = page.rotationAngle % 180 != 0
        let native = rotated ? CGSize(width: box.height, height: box.width) : box.size
        let scale = CGFloat(width) / native.width
        let size = CGSize(width: CGFloat(width), height: (native.height * scale).rounded())

        guard let ctx = CGContext(
            data: nil, width: Int(size.width), height: Int(size.height),
            bitsPerComponent: 8, bytesPerRow: 0, space: CGColorSpaceCreateDeviceRGB(),
            bitmapInfo: CGImageAlphaInfo.noneSkipLast.rawValue
        ) else { continue }
        ctx.setFillColor(CGColor(red: 1, green: 1, blue: 1, alpha: 1))
        ctx.fill(CGRect(origin: .zero, size: size))
        // The drawing transform only ever scales down, so fit the page at its
        // own size and scale the whole context up to the target width.
        ctx.scaleBy(x: scale, y: scale)
        ctx.concatenate(page.getDrawingTransform(.cropBox, rect: CGRect(origin: .zero, size: native), rotate: 0, preserveAspectRatio: true))
        ctx.drawPDFPage(page)

        guard let image = ctx.makeImage() else { continue }
        if n == 1 { cover = image }
        bytes += writeJPEG(image, to: out.appendingPathComponent(String(format: "%02d.jpg", n)), quality: quality)
    }

    // The share card, in a sibling folder named for the PDF.
    let shareDir = pdf.deletingLastPathComponent().appendingPathComponent("share")
    try fm.createDirectory(at: shareDir, withIntermediateDirectories: true)
    var shareBytes = 0
    if let cover = cover, let card = shareCard(from: cover) {
        shareBytes = writeJPEG(card, to: shareDir.appendingPathComponent(pdf.deletingPathExtension().lastPathComponent + ".jpg"), quality: shareQuality)
    }
    print("ok    \(pdf.lastPathComponent): \(doc.numberOfPages) slides, \(bytes / 1024) KB; share card \(shareBytes / 1024) KB → \(out.path.replacingOccurrences(of: root.path + "/", with: ""))")
}
exit(failed ? 1 : 0)
