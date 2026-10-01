#!/usr/bin/env python3
"""Verify singular template bodies against a JSON list of public post URLs/titles.

Each item has url, title and type (resource, timeline or live_session).
An optional heading accounts for editorial title filters.
Use --base to run the same list against a rehearsal or the live site.
"""
import argparse
import concurrent.futures
import html
from html.parser import HTMLParser
import json
import re
import urllib.parse
import urllib.request


class Headings(HTMLParser):
    def __init__(self):
        super().__init__()
        self.in_h1 = False
        self.headings = []
        self.current = []

    def handle_starttag(self, tag, attrs):
        if tag == 'h1':
            self.in_h1 = True
            self.current = []

    def handle_endtag(self, tag):
        if tag == 'h1':
            self.headings.append(' '.join(''.join(self.current).split()))
            self.in_h1 = False

    def handle_data(self, text):
        if self.in_h1:
            self.current.append(text)


def normalized_title(value):
    # WordPress texturizes straight quotes and dashes when printing titles.
    return ' '.join(html.unescape(value).translate(str.maketrans({'‘': "'", '’': "'", '“': '"', '”': '"', '–': '-', '—': '-'})).split())


def verify(page, base):
    url = page['url']
    if base:
        old = urllib.parse.urlsplit(url)
        url = base.rstrip('/') + old.path + ('?' + old.query if old.query else '')
    try:
        request = urllib.request.Request(url, headers={'User-Agent': 'AiAd27-detail-page-verification'})
        with urllib.request.urlopen(request, timeout=30) as response:
            body = response.read().decode('utf8', 'replace')
            if response.status != 200:
                return {'url': url, 'error': f'HTTP {response.status}'}
        parser = Headings()
        parser.feed(body)
        expected = normalized_title(page.get('heading', page['title']))
        if expected not in [normalized_title(heading) for heading in parser.headings]:
            return {'url': url, 'error': 'Requested post title is missing from the rendered body'}
        if page['type'] == 'resource' and not re.search(r'<article[^>]*\brl-article\b', body):
            return {'url': url, 'error': 'Lesson article missing'}
        if re.search(r'Fatal error|Warning:|Notice:|Deprecated:|Parse error', body):
            return {'url': url, 'error': 'PHP diagnostics in page output'}
    except Exception as error:
        return {'url': url, 'error': str(error)}
    return None


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('pages', help='JSON file with the public post URL/title/type list')
    parser.add_argument('--base', help='Override the site origin for rehearsal testing')
    args = parser.parse_args()
    with open(args.pages) as source:
        pages = json.load(source)
    with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
        failures = [result for result in pool.map(lambda page: verify(page, args.base), pages) if result]
    print(json.dumps({'checked': len(pages), 'passed': len(pages) - len(failures), 'failures': failures}, indent=2))
    raise SystemExit(bool(failures))
