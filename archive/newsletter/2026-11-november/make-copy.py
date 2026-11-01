# One source for the November newsletter: writes the Beehiiv copy (markdown) and the site update (block markup).
# Run from the theme root: python3 archive/newsletter/2026-11-november/make-copy.py
import html, re
SITE='SITE_URI'
doc=[]  # (kind, payload)
def P(t): doc.append(('p',t))
def H(t): doc.append(('h',t))
def I(n,alt): doc.append(('img',(n,alt)))
def B(l,u): doc.append(('btn',(l,u)))
def L(items): doc.append(('ul',items))
def O(items): doc.append(('ol',items))
def A(t,u): return f'<a href="{u}">{t}</a>'

I('01-oracy-is-back','November newsletter. Oracy is back: speaking joins reading and writing in England’s national curriculum. Spring 2027, final curriculum published. 29 April 2027, AI Awareness Day. September 2028, first teaching.')
P('Hello,')
P('Oracy is back on the curriculum. The Education Secretary has announced that oracy (public speaking, structured debate and attentive listening) will sit alongside reading and writing in England’s national curriculum, as a new strand of English assessed up to and including GCSE.')
L(['<strong>Spring 2027:</strong> the final revised national curriculum is due to be published.','<strong>September 2028:</strong> first teaching of the new curriculum begins, cementing oracy in classrooms across England.'])
P('It’s perfect timing for AI Awareness Day 2027. The National AI Conversation, which opens on 1 January, exists to build exactly these skills: young people questioning, discussing and debating the role AI should play in their lives, building to AI Awareness Day on <strong>Thursday 29 April 2027</strong>.')
H('Why oracy, why now')
L(['<strong>The AI imperative.</strong> As generative AI takes over more routine writing and recall, the distinctly human skills (reasoning, debate and structured conversation) are what future-proof students. When a machine can draft the essay, the value shifts to the person who can explain and defend it.',
   '<strong>Dismantling privilege.</strong> Confident speaking has long been a marker of privilege. The reform aims to make structured communication every child’s right, levelling the playing field for further study and careers.',
   '<strong>Academic and social growth.</strong> Education campaigners point to evidence that teaching oracy explicitly raises attainment, builds confidence and supports students’ wellbeing.'])
P('Read more: '+A('BBC News','https://www.bbc.co.uk/news/articles/cqkglk84j88ro')+' and the '+A('English-Speaking Union','https://www.esu.org/news-and-views/oracy-to-sit-alongside-reading-and-writing-in-the-national-curriculum/')+'.')
B('Read our oracy article',SITE+'/timeline/oracy-and-ai-awareness-day/')
H('Eight skills'); I('11-eight-skills','The skills that stay human. Eight skills for the age of AI: oracy, public speaking, critical thinking, information literacy, conflict resolution, active listening, independent judgement and civil discourse.')
P('AI can answer almost any question in seconds. So what should young people be learning? We think the answer is the skills no machine can practise for them, and oracy comes first:')
O(['<strong>Oracy &amp; Communication:</strong> fluent spoken language, confidence and the ability to articulate complex ideas',
   '<strong>Public Speaking:</strong> being articulate and exploring technical concepts in front of an audience',
   '<strong>Critical Thinking:</strong> evaluating arguments logically and spotting cognitive biases',
   '<strong>Information Literacy:</strong> examining evidence, identifying bias and verifying facts',
   '<strong>Conflict Resolution:</strong> resolving conflict in pressured situations',
   '<strong>Active Listening &amp; Empathy:</strong> engaging deeply with other people’s viewpoints and responding respectfully',
   '<strong>Independent Judgement:</strong> forming convictions backed by reasoning rather than peer pressure',
   '<strong>Civil Discourse:</strong> adapting tone and voice to defuse tension and solve problems together'])
P('Which of these skills matters most to you? Just reply and tell us.')
P('That’s why we’re launching the National AI Conversation: young people across the UK debating the big questions about AI, in their classrooms, their schools and with schools across the country.')
B('Explore the National Conversation',SITE+'/national-conversation/')
P('<strong>Also in October:</strong> AI Awareness Day landed in the BBC Bitesize calendar, teachers around the world took our free AI audit, two new partners joined and our lesson starters got a refresh. And on World Teachers’ Day we said thank you to the real humans in the loop: teachers, who help young people question AI, not just use it. Here it all is.')
H('In BBC Bitesize'); I('02-bbc-bitesize-calendar','Teachers’ calendar. On the page teachers plan from: AI Awareness Day is now in the BBC Bitesize calendar for April. A calendar for April 2027 with Thursday 29 April marked.')
P('AI Awareness Day is now in the BBC Bitesize educational calendar for teachers. Every year, Bitesize maps out the dates schools plan around, month by month: Earth Day, Shakespeare Day, Stephen Lawrence Day and more. Thursday 29 April 2027 is now one of them, right where teachers look when they plan the school year.')
B('See the April calendar','https://www.bbc.co.uk/teach/topics/c9wk4kxgkxjt')
H('Lesson starters'); I('04-lesson-starters','Lesson starters. Five minutes, one big question. Five theme tiles with their takeaways: Safe, think before you post. Smart, autocomplete, not an oracle. Creative, you are still the author. Responsible, thoughtfully, not never. Future, collaborate, and stay human.')
P('You don’t have to wait for January. Each of our five themes (Safe, Smart, Creative, Responsible and Future) is now a free five-minute lesson starter: a simple way to help students understand the AI landscape before they start to argue about it.')
P('Every starter has one big question, a UK fact to react to, three habits to take away, and slides and a lesson plan ready to go. Most also have a debate for primary, secondary and post-16. New this month: the slides show straight on the board, and you can tick off the steps you’ll skip.')
B('Try one tomorrow',SITE+'/resources/')
H('Free AI audit'); I('03-free-ai-audit','Free AI audit. How much do you rely on AI? Teachers around the world have taken it. For teachers, students, parents and leaders. Free, 10 to 15 minutes, anonymous.')
P('How much do you rely on AI? Do you check what it tells you? Could you manage without it? Most AI audits ask whether a school has a policy. Few ask what people actually do, so we built a free one that does.')
P('The AI Risk &amp; Readiness Benchmark takes 10–15 minutes, it’s anonymous, and there’s a version for teachers, students, parents and school leaders. You get a readiness score and tailored next steps, and schools can build a picture across their whole community. No student personal data is collected. Teachers around the world have already taken it: thank you.')
B('Take the AI audit',SITE+'/timeline/ai-risk-readiness-benchmark/')
H('Worth a read'); I('06-human-bottleneck','Worth a read. Is the human the new bottleneck? What Anthropic’s report on its own workplace means for schools and careers. 80% of AI session time at Anthropic is spent waiting for a person.')
P('Human in the loop? Why, if they slow things up checking everything? Anthropic’s latest report found that AI sessions inside the company spend roughly 80% of their time waiting for a person, and the junior share of technical hires fell from 27% to 9%. So what does that mean for teachers, for pupils, and for the career advice we give young people?')
B('Read the article',SITE+'/timeline/human-bottleneck-ai-workplace/')
H('A debate to try'); I('10-dessa-classroom-debate','Classroom debate. If AI sounded human, would you know? A voice AI that laughs, pauses and says mm-hmm. Four questions to debate, one each for Safe, Smart, Responsible and Future.')
P('If an AI sounded exactly like a person, would you still keep a human in the loop?')
P('Block (Jack Dorsey’s company) has unveiled Dessa, a research voice AI that learned to talk from recordings of real conversation, with no written text at all. Block says it can listen and speak at the same time, laugh mid-sentence and pick up on pauses and sarcasm. These are the company’s own claims; there are no independent tests yet. It makes a perfect classroom debate:')
L(['<strong>Safe:</strong> if a voice on the phone sounds human, how would you check?','<strong>Smart:</strong> when should a voice AI act for you, and when should you decide yourself?','<strong>Responsible:</strong> should an AI have to tell you it isn’t human?','<strong>Future:</strong> if AI can chat like us, what conversation skills must stay human?'])
P('Which side would your students take? Block’s page: '+A('block.xyz/dessa','https://block.xyz/dessa'))
H('New partners'); I('05-new-partners','New event partners. Welcome to the loop: AILitKit, AI literacy in the lessons you already teach, and DigitALL, Spark AI and EcoCode, free for schools.')
P('<strong>AILitKit</strong> helps teachers bring AI literacy into the lessons they already teach, in any subject and year group. Every guide includes an unplugged activity, and pupils never need an account. '+A('ailitkit.com/ai-literacy','https://ailitkit.com/ai-literacy'))
P('<strong>DigitALL</strong> is a charity building young people’s digital skills through climate and sustainability. Its free '+A('Spark AI','https://digitall.charity/sparkai')+' and '+A('EcoCode','https://digitall.charity/ecocode')+' resources are now in Extra Resources on our homepage. Thank you to Peter Marshman and the DigitALL team.')
B('Meet our partners',SITE+'/partners/')
H('Partner with us'); I('12-partner-with-us','Partner with us. Join the loop: we’re welcoming new partners and sponsors for AI Awareness Day 2027. The five theme tiles in a ring, with one open place marked Your organisation.')
P('In 2026 we engaged hundreds of thousands of young people, working with the UK’s computing and education bodies, including BCS, Computing at School, the National Centre for Computing Education, STEM Learning and the Raspberry Pi Foundation. Six multi-academy trusts, covering 192 schools and around 136,500 students, also worked with us.')
P('2026 was about resources: helping schools build AI literacy. 2027 is a new season. From January, the National Conversation brings schools together to debate each other across the country, in person and online, and running it well takes more than ever before. So for the first time, we’re inviting organisations to support the campaign directly, as partners and sponsors.')
P('There’s room for different kinds of support, from sharing the campaign with your networks or contributing classroom resources, to supporting the National Debate Network or sponsoring the day itself. We’ll work with you to find the fit that makes sense for you and for schools.')
P('If that sounds like your organisation, we’d love to talk. Get in touch through our contact form (choose “Organisation”) or simply reply to this email.')
P('<strong>Already a partner?</strong> Send us your latest resources, share your referral route with schools in your network, volunteer to judge a debate, or introduce us to organisations, schools and MATs who should be part of the National Conversation.')
B('Talk to us about partnering',SITE+'/#contact')
H('Debate pilots'); I('07-debate-pilots','Debate Network. Piloting with schools: our first school debates start this month. November 2026 pilot sessions for primary, secondary and post-16.')
P('This month we start piloting the National Debate Network with schools: primary, secondary and post-16 students debating the big questions about AI, with judges, scorecards and a final vote.')
P('Want to try it out now? Head over to our lesson resources. Most lesson starters come with a ready-made debate and motions for every age, so you can run one in your classroom this week.')
P('We’ll share more on how your school can get involved early next year, ahead of the National AI Conversation opening on 1 January 2027.')
B('Try a classroom debate',SITE+'/resources/')
H('How to take part'); I('06b-ways-to-engage','More ways to engage. Pick your level: start with one class, go as far as a national debate. Classroom Conversation, School Debate, MAT Debate and the National Debate Network.')
P('From January, primary (Years 5–6), secondary (Years 7–11) and post-16 students (Years 12–13 and FE) can take part. Choose the level that suits your school:')
L(['<strong>Classroom Conversation:</strong> one theme, a five-minute starter and a class discussion.','<strong>School Debate:</strong> class vs class, house vs house or year vs year.','<strong>MAT Debate:</strong> schools across your trust debate each other.','<strong>National Debate Network:</strong> our flagship, debating schools beyond your own, judged with our national scorecard.'])
H('Teacher Ambassadors')
P('Have you led a debate club at your school? Your experience could help shape the National Conversation. We’re rolling out a small number of Teacher Ambassador roles: to find out more, email '+A('info@aiawarenessday.co.uk','mailto:info@aiawarenessday.co.uk')+'.')
H('We’re on Instagram'); I('08-instagram','We’re on Instagram. Follow along: a big question a week, and classroom ideas you can use tomorrow. @aiawarenessday')
P('Follow @aiawarenessday for a big question a week, classroom ideas you can use tomorrow, and a look behind the scenes as we build the National AI Conversation.')
P('We’re also looking for teachers’ and school leaders’ hot takes on AI. Post yours on Instagram and invite @aiawarenessday as a collaborator (Tag people, then Invite collaborator). We’ll share the best ones on our profile.')
B('Follow us','https://www.instagram.com/aiawarenessday/')
H('Around the world')
P('We’re speaking to leads in Africa, the Caribbean and Europe about running AI Awareness Day in their own countries, independently or in sync with us, the way AI Awareness Day UK works with AI Awareness Day US. Interested? Drop us a line at '+A('info@aiawarenessday.co.uk','mailto:info@aiawarenessday.co.uk')+'.')
H('Coming up'); I('09-coming-up','Coming up. November to December 2026: pilot sessions with schools and trusts. 1 January 2027: the National Conversation opens. 29 April 2027: AI Awareness Day.')
L(['<strong>November – December 2026:</strong> pilot sessions with schools and trusts','<strong>1 January 2027:</strong> the National AI Conversation opens','<strong>Spring 2027:</strong> the final revised national curriculum is due','<strong>Thursday 29 April 2027:</strong> AI Awareness Day'])

def block(k,v,site):
    if k=='p': return f'<!-- wp:paragraph -->\n<p>{v}</p>\n<!-- /wp:paragraph -->'
    if k=='h': return f'<!-- wp:heading {{"textColor":"responsible-deep"}} -->\n<h2 class="wp-block-heading has-responsible-deep-color has-text-color">{v}</h2>\n<!-- /wp:heading -->'
    if k=='img': return f'<!-- wp:image {{"sizeSlug":"full","linkDestination":"none"}} -->\n<figure class="wp-block-image size-full"><img src="IMG:{v[0]}" alt="{html.escape(v[1])}"/></figure>\n<!-- /wp:image -->'
    if k=='btn': return f'<!-- wp:buttons -->\n<div class="wp-block-buttons"><!-- wp:button -->\n<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{v[1]}">{v[0]}</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->'
    if k=='ol': return '<!-- wp:list {"ordered":true} -->\n<ol class="wp-block-list">'+''.join(f'<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->' for i in v)+'</ol>\n<!-- /wp:list -->'
    if k=='ul': return '<!-- wp:list -->\n<ul class="wp-block-list">'+''.join(f'<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->' for i in v)+'</ul>\n<!-- /wp:list -->'
# On the website there is no email to reply to.
WEB={' or simply reply to this email.':'.','Which of these skills matters most to you? Just reply and tell us.':'Which of these skills matters most to you? Tell us on <a href="https://www.instagram.com/aiawarenessday/">Instagram</a> or LinkedIn.'}
def web(v):
    if isinstance(v,str):
        for a,b in WEB.items(): v=v.replace(a,b)
    return v
site=[block(k,web(v),True) for k,v in doc if not (k=='p' and v=='Hello,')]
site.append(block('p','Get every newsletter in your inbox: <a href="https://aiawarenessday.beehiiv.com/subscribe">subscribe for free</a>.',True))
open('inc/articles/november-2026-newsletter.html','w').write('\n\n'.join(site)+'\n')

def md(t):
    t=re.sub(r'<a href="([^"]+)">([^<]+)</a>',r'[\2](\1)',t); t=re.sub(r'</?strong>','**',t); return t.replace('&amp;','&').replace('SITE_URI','https://aiawarenessday.co.uk')
out=['# November 2026 newsletter: Oracy is back (plus the October round-up)','',
'Banners: `beehiiv-upload/` (transparent, 1200×600), made by `archive/design-kit/nov-banners.html` + `render-nov-banners.mjs`. Superseded banners are in `superseded/`. The website version is `inc/articles/november-2026-newsletter.html`; both are written by one script, so they say the same thing.','',
'- **Title / subject (31 characters):** Oracy is back on the curriculum',
'- **Subtitle / preview text (70 characters):** What it means for AI Awareness Day 2027, plus October in one read.','','---','']
for k,v in doc:
    if k=='p': out+= [md(v),'']
    elif k=='h': out+= ['## '+md(v),'']
    elif k=='img': out+= [f'[{v[0]}.png] Alt text: {v[1]}','']
    elif k=='btn': out+= [f'Button: **{v[0]}** → {md(v[1])}','']
    elif k=='ol': out+= [f'{n}. '+md(i) for n,i in enumerate(v,1)]+['']
    elif k=='ul': out+= ['- '+md(i) for i in v]+['']
out+=["Know a colleague who'd enjoy this? Forward it on, or send them to aiawarenessday.co.uk to sign up. And just reply to this email to tell us what you'd like to see next.",'','Keep Humans in the Loop,','The AI Awareness Day team','']
open('archive/newsletter/2026-11-november/newsletter-copy.md','w').write('\n'.join(out))
