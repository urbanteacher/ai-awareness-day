=== AI Awareness Day ===
Contributors: aiawarenessday
Requires at least: 7.1
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.7.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The block theme for AI Awareness Day, a national campaign that helps schools, students and parents explore AI.

== Description ==

AI Awareness Day is a block theme. Every template is a block template, and pages, posts, archives, the homepage,
header and footer are edited in the block editor and the Site Editor.

The theme handles presentation. Content types, forms, tracking, SEO, the benchmark and the debate network are in
three plugins that ship in this repository's plugins/ folder and that the theme installs and activates itself:

* AI Awareness Day Core (plugins/aiad-core)
* AI Risk & Readiness Benchmark (plugins/ai-risk-readiness-benchmark)
* AI Awareness Day Debate Network (plugins/aiad-debate-network)

The self-paced courses are served at /courses/ and the seasonal challenges at /challenges/ (inc/courses.php).

Deploying: see docs/DEPLOY.md in the repository.

== Changelog ==

= 1.7.0 =
* Courses and challenges get their own addresses, /courses/ and /challenges/.
* Admin, contact, tracking, REST client and share scripts moved into the AI Awareness Day Core plugin.
* One-off checks no longer run on every request.

== Copyright ==

AI Awareness Day WordPress Theme, Copyright AI Awareness Day.
AI Awareness Day is distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

== Resources ==

Uncut Sans (the AIAD Sans font, assets/fonts/aiad27/)
Copyright (c) 2022 Kasper Nordkvist, https://uncut.wtf/sans-serif/uncut-sans.html
License: SIL Open Font License, Version 1.1, https://openfontlicense.org (assets/fonts/aiad27/UncutSans-LICENSE.txt)

National Conversation photographs (assets/images/national-conversation/)
Source: Unsplash, https://unsplash.com
License: Unsplash License, https://unsplash.com/license

Each photograph is credited to its photographer on the page (patterns/national-conversation.php).

Partner logos (assets/images/logos/) belong to their organisations.
