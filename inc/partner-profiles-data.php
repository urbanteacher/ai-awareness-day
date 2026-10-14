<?php
/**
 * Partner profile details, keyed by partner slug, for the partner profiles migration (inc/partner-profiles.php).
 *
 * Drafted from each partner's own website in October 2026. Fields: title, type (partner_type slug), website, intro
 * (one sentence), bio (paragraphs), ai_resources_url (leave empty: a link here puts the partner first on the homepage grid as an "AI resources" card; only AILitKit has one for now), links (theme, title, url). 'featured_resources' add tiles to the homepage's Extra Resources; 'fix_title' / 'fix_type' / 'fix_logo' replace a
 * wrong value already on the site; 'create' adds the partner if missing, with 'logo' from assets/images/logos.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'aied-certified' => array(
		'title'            => 'AiEd Certified',
		'type'             => 'charity',
		'website'          => 'https://www.ai-in-education.co.uk/',
		'intro'            => 'AiEd Certified is a certification framework from the charity AI in Education that helps schools and colleges build and recognise safe, purposeful use of AI.',
		'bio'              => array(
			'AiEd Certified is run by AI in Education, a registered charity. Its framework has three levels, Explorer, Practitioner and Innovator, covering AI literacy, policies and ethics, tools and systems, digital pedagogy, and collaboration.',
			'Schools gather evidence they already hold, such as policies and training materials, and expert assessors give supportive feedback. The charity also hosts a searchable library of AI resources for teachers and leaders, including case studies, guidance reports and prompts.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Library of AI resources for teachers and leaders', 'url' => 'https://www.ai-in-education.co.uk/resources' ),
			array( 'theme' => 'responsible', 'title' => 'How AiEd Certified works for schools', 'url' => 'https://www.ai-in-education.co.uk/how-it-works' ),
		),
	),
	'apps-for-good' => array(
		'title'            => 'Apps for Good',
		'type'             => 'charity',
		'website'          => 'https://www.appsforgood.org/',
		'intro'            => 'Apps for Good is an education charity offering a free, project-based computing course in which students design and build AI-powered apps for social impact.',
		'bio'              => array(
			'Apps for Good is a UK education charity that gives young people computing, AI and life skills through project-based learning. Its course is free to schools, supported by partners and industry volunteers.',
			'Its AI for Good 2.0 course is a modular 12-session programme for KS3 (ages 11 to 14). Students can code in JavaScript or use an AI-assisted Canva Code pathway, and explore AI bias, ethics and deepfakes without needing personal data.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'creative', 'title' => 'AI for Good 2.0: build AI-powered apps, KS3 (free)', 'url' => 'https://www.appsforgood.org/our-course' ),
			array( 'theme' => 'responsible', 'title' => 'AI for Good 2.0 FAQs: bias, ethics and data safety', 'url' => 'https://www.appsforgood.org/ai-for-good-2-0-frequently-asked-questions' ),
			array( 'theme' => 'future', 'title' => 'Career Series: short sessions with industry volunteers', 'url' => 'https://www.appsforgood.org/career-series' ),
		),
	),
	'barefoot-computing' => array(
		'title'            => 'Barefoot Computing',
		'type'             => 'charity',
		'website'          => 'https://www.barefootcomputing.org/',
		'intro'            => 'Barefoot Computing gives primary teachers free computing resources, workshops and live lessons, including AI Explorers lessons on how AI works and how to use it safely.',
		'bio'              => array(
			'Barefoot Computing supports primary teachers to teach computing to children aged 2 to 11. Set up in 2014, it is powered by BCS, The Chartered Institute for IT, and its Computing at School network, with support from BT, Ciena and the Scratch Foundation.',
			'It offers cross-curricular lesson plans, live lessons, CPD workshops and jargon-free guides. Its AI Explorers resources help pupils learn how AI is trained and spot AI-generated misinformation.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Barefoot AI Explorers Level 1, ages 5 to 7', 'url' => 'https://www.barefootcomputing.org/resources/ai-explorers-level-1' ),
			array( 'theme' => 'safe', 'title' => 'Barefoot AI Explorers Level 2, ages 7 to 11', 'url' => 'https://www.barefootcomputing.org/resources/ai-explorers-level-2' ),
			array( 'theme' => 'creative', 'title' => 'Primary computing lessons by age group', 'url' => 'https://www.barefootcomputing.org/primary-computing-resources' ),
		),
	),
	'bbc-bitesize' => array(
		'title'            => 'BBC Bitesize',
		'type'             => 'broadcaster',
		'fix_type'         => true,
		'website'          => 'https://www.bbc.co.uk/bitesize',
		'intro'            => 'BBC Bitesize is the BBC\'s free learning service, with curriculum content, careers advice and articles helping young people understand, question and use AI.',
		'bio'              => array(
			'BBC Bitesize is the BBC\'s online learning service for pupils, parents and teachers, with curriculum-linked guides, videos and quizzes from early years to post-16, plus careers content through Bitesize Careers.',
			'Its articles on AI help young people use AI for homework without risking their grades, spot deepfakes, and test themselves with AI or real quizzes. Careers pieces explain how AI is used in job applications.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'How to use AI for homework', 'url' => 'https://www.bbc.co.uk/bitesize/articles/zf7xkhv' ),
			array( 'theme' => 'safe', 'title' => 'How to spot a deepfake', 'url' => 'https://www.bbc.co.uk/bitesize/articles/zbkr7v4' ),
			array( 'theme' => 'future', 'title' => 'How AI is being used in your job application', 'url' => 'https://www.bbc.co.uk/bitesize/articles/z3sc96f' ),
		),
	),
	'bcs-the-chartered-institute-for-it' => array(
		'title'            => 'BCS – The Chartered Institute for IT',
		'type'             => 'institute',
		'website'          => 'https://www.bcs.org/',
		'intro'            => 'BCS, The Chartered Institute for IT, is the professional body for IT, offering schools free AI Confidence training for staff, computing teacher support and digital skills qualifications.',
		'bio'              => array(
			'BCS, The Chartered Institute for IT, is the professional body for digital and IT professionals, operating under a Royal Charter. It supports computing education through teacher scholarships, subject knowledge courses, digital skills qualifications and the Barefoot and Computing at School programmes.',
			'Its AI Confidence programme offers free short online modules for anyone working in a UK school or college, covering AI basics, school leadership, inclusion and AI in the world of work. BCS membership is not required.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'AI Confidence: short AI training for school staff (free)', 'url' => 'https://www.bcs.org/deliver-and-teach-qualifications/teachers-schools-and-colleges/ai-confidence/' ),
			array( 'theme' => 'responsible', 'title' => 'AI Confidence Toolkit for schools', 'url' => 'https://www.bcs.org/deliver-and-teach-qualifications/teachers-schools-and-colleges/ai-confidence/ai-confidence-toolkit/' ),
			array( 'theme' => 'future', 'title' => 'Starting your IT career', 'url' => 'https://www.bcs.org/it-careers/' ),
		),
	),
	'bett' => array(
		'title'            => 'Bett',
		'type'             => 'events-company',
		'website'          => 'https://uk.bettshow.com/',
		'intro'            => 'Bett is the UK\'s annual education technology show, free for educators, with talks, exhibitors and free reports on how teachers and students use AI.',
		'bio'              => array(
			'Bett, the British Educational Technology Show, started in 1985 and brings educators and edtech providers together each January at ExCeL London. Bett UK 2027 runs from 20 to 22 January 2027, and educators attend free.',
			'The programme includes talks, hands-on tech labs and CPD-accredited content. Its content hub offers free reports on AI in education, including a survey of UK teachers and a report on what students think about AI.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'The state of AI in education 2026 report (free)', 'url' => 'https://uk.bettshow.com/reports-and-whitepapers/state-of-ai-in-education' ),
			array( 'theme' => 'future', 'title' => 'Learner AI report: students on AI and the future (free)', 'url' => 'https://uk.bettshow.com/learner-ai-report' ),
			array( 'theme' => 'future', 'title' => 'Register for Bett UK', 'url' => 'https://uk.bettshow.com/register-interest' ),
		),
	),
	'black-futures-ai' => array(
		'title'            => 'Black AI Futures',
		'fix_title'        => true,
		'type'             => 'tech-network',
		'website'          => 'https://blackaifutures.org/',
		'intro'            => 'Black AI Futures is a UK Black-led AI for Good network that works on policy, research and education so AI benefits Black and Global Majority communities.',
		'bio'              => array(
			'Black AI Futures (BAIF) was founded in 2025 as a strategic policy initiative, part think-tank, part convener and part community organiser. It works to make sure AI technologies empower Black and Global Majority communities, through advocacy, education and ethical investment.',
			'BAIF runs workshops, public reports and educational programmes that explain AI and build community capacity. Its education work focuses on supplementary education and SEND, aiming for AI tools that support rather than replace educators. It also publishes an Ethical AI Repository that rates AI tools for bias and transparency.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'responsible', 'title' => 'Ethical AI Repository: AI tools rated for bias', 'url' => 'https://blackaifutures.org/repository' ),
			array( 'theme' => 'responsible', 'title' => 'Reports, policy briefs and open evidence on AI', 'url' => 'https://blackaifutures.org/resources' ),
		),
	),
	'bourne-education-trust-bet' => array(
		'title'            => 'Bourne Education Trust (BET)',
		'type'             => 'mat',
		'fix_type'         => true,
		'website'          => 'https://www.bourne.education/',
		'intro'            => 'Bourne Education Trust is a multi-academy trust in Surrey, Hampshire and London that co-founded the AI in Education initiative and leads on digital learning in schools.',
		'bio'              => array(
			'Bourne Education Trust (BET) was established in 2011. It is a multi-academy trust of primary, secondary, specialist and alternative provision academies, plus associate schools, across Hampshire, London and Surrey. Its purpose is "transforming schools, changing lives".',
			'Through its BET Digital strategy the trust aims to be a leading UK trust for digital learning, teaching and operations. BET co-founded the cross-sector AI in Education initiative with Epsom College and describes itself as the first AiEd-certified multi-academy trust in the UK.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'AI in Education: BET and Epsom College initiative', 'url' => 'https://www.bourne.education/bet-learns/bet-digital-1/ai-in-education' ),
		),
	),
	'capital-city-college' => array(
		'title'            => 'Capital City College',
		'type'             => 'fe',
		'website'          => 'https://www.capitalccg.ac.uk/',
		'intro'            => 'Capital City College is a large London further education college offering 16 to 18 and adult courses in computing, digital skills and AI, including AI generative media.',
		'bio'              => array(
			'Capital City College is a further education college group in London, with campuses including Angel, King\'s Cross, Holloway, Tottenham, Enfield, Soho and Westminster. It offers A Levels, T Levels, vocational and technical courses, apprenticeships, higher education and short courses for young people and adults.',
			'Its computing and IT courses cover programming, digital design, cyber security, data and AI. A Level 3 UAL course in AI Generative Media at Holloway teaches 16 to 18 year olds generative tools and the ethics of AI content. Study is free for eligible 16 to 18 year olds.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'creative', 'title' => 'AI Generative Media, Level 3 (UAL) course', 'url' => 'https://www.capitalccg.ac.uk/course/ai-generative-media-level-3-diploma/46061/' ),
			array( 'theme' => 'future', 'title' => 'Computing and IT courses in London', 'url' => 'https://www.capitalccg.ac.uk/courses/computing-and-it/562/' ),
			array( 'theme' => 'future', 'title' => 'Digital Bootcamps for adults', 'url' => 'https://www.capitalccg.ac.uk/courses/category?frmCatWeb=644' ),
		),
	),
	'central-district-alliance' => array(
		'title'            => 'Central District Alliance',
		'type'             => 'organisation',
		'fix_type'         => true,
		'website'          => 'https://www.centraldistrictalliance.com/',
		'intro'            => 'Central District Alliance is the not-for-profit Business Improvement District for Holborn, Bloomsbury, St Giles, Clerkenwell and Farringdon, linking local businesses with schools and skills programmes.',
		'bio'              => array(
			'Central District Alliance (CDA) is the Business Improvement District for Holborn, St Giles, Bloomsbury, Clerkenwell and Farringdon in Camden and Islington. Formerly Midtown BID, it is a not-for-profit company limited by guarantee representing over 400 member businesses.',
			'CDA works on a clean, safe and welcoming district, placemaking, events and advocacy. Its ESG programme includes employability skills and training for local residents, and work to link local businesses with schools in the district to create pathways to future opportunities.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'future', 'title' => 'ESG: employability and links with local schools', 'url' => 'https://www.centraldistrictalliance.com/project-page-esg/' ),
		),
	),
	'chiltern-learning-trust' => array(
		'title'            => 'Chiltern Learning Trust',
		'type'             => 'mat',
		'fix_type'         => true,
		'website'          => 'https://www.chilternlearningtrust.org/',
		'intro'            => 'Chiltern Learning Trust is a multi-academy trust of 21 schools in Bedfordshire and Luton that also trains teachers and supports staff development.',
		'bio'              => array(
			'Chiltern Learning Trust is a multi-academy trust, a family of 21 schools across Bedfordshire and Luton. Its mission is to equip every child to lead a happy, successful life as a responsible citizen, whatever their background. It was named MAT of the Year in 2025.',
			'The trust runs the Chiltern Training Group, a school-centred initial teacher training provider, and the Chiltern Teaching School Hub, which offers professional development for staff at every career stage. Pupils take part in enrichment such as Climate Change and Digital Innovation summits.',
		),
		'ai_resources_url' => '',
		'links'            => array(),
	),
	'common-sense-media' => array(
		'title'            => 'Common Sense Media',
		'type'             => 'non-profit',
		'fix_type'         => true,
		'website'          => 'https://www.commonsensemedia.org/',
		'intro'            => 'Common Sense Media is a nonprofit whose Common Sense Education arm offers free digital literacy and AI literacy lessons, teacher courses and guidance for schools.',
		'bio'              => array(
			'Common Sense Media is a US nonprofit that helps children, families and educators navigate media and technology. Its Common Sense Education site offers a free Digital Literacy and Well-Being curriculum with more than 165 lessons, covering privacy, online safety, media literacy and AI literacy.',
			'For AI, it offers short AI literacy lessons on topics such as bias, chatbots, algorithms and deepfakes, plus free self-paced courses for teachers, including AI Basics for K-12 Teachers. It also publishes AI risk assessments of popular AI tools and a toolkit for schools adopting AI.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'AI literacy lessons for students', 'url' => 'https://www.commonsense.org/education/collections/ai-literacy-lessons-for-grades-k-12' ),
			array( 'theme' => 'smart', 'title' => 'Self-paced AI courses for teachers (free)', 'url' => 'https://www.commonsense.org/education/AI-PD-For-Educators' ),
			array( 'theme' => 'safe', 'title' => 'Digital Literacy and Well-Being curriculum (free)', 'url' => 'https://www.commonsense.org/education/digital-literacy' ),
		),
	),
	'computing-at-school-cas' => array(
		'title'            => 'Computing at School (CAS)',
		'type'             => 'institute',
		'website'          => 'https://www.computingatschool.org.uk/',
		'intro'            => 'Computing at School (CAS) is the UK community for computing teachers, sharing classroom resources, CPD and events, including an AI Zone with lessons on AI literacy.',
		'bio'              => array(
			'Computing at School (CAS) is a nationwide community of computing educators, powered by BCS, The Chartered Institute for IT. It supports teachers from primary to higher education through regional and online communities, a discussion forum, events and an annual conference.',
			'Its resource library is built by teachers for teachers. The CAS AI Zone gathers lesson resources, AI policy templates and CPD, including signposting to AI Confidence, free CPD for school staff created by BCS and partners. Other themes cover physical computing, 3D and gaming, and digital literacy.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Artificial intelligence: 5-lesson unit for Years 5 and 6', 'url' => 'https://www.computingatschool.org.uk/resources/2026/may/artificial-intelligence-5-lesson-computing-unit-for-y56/' ),
			array( 'theme' => 'responsible', 'title' => 'How to use AI responsibly (ages 13+)', 'url' => 'https://www.computingatschool.org.uk/resources/2024/june/how-to-use-ai-responsibly/' ),
			array( 'theme' => 'creative', 'title' => 'Build your first AI app with Python', 'url' => 'https://www.computingatschool.org.uk/resources/2026/may/build-your-first-ai-app-with-python-and-ai/' ),
		),
	),
	'crafting-tomorrow' => array(
		'title'            => 'Crafting Tomorrow',
		'type'             => 'edtech',
		'website'          => 'https://crafting-tomorrow.com/',
		'intro'            => 'Crafting Tomorrow is a European edtech company offering schools Living with AI, a PSHE-based AI literacy curriculum for Years 3 to 13, plus tools for parents.',
		'bio'              => array(
			'Crafting Tomorrow is a European education company co-founded by Kevin D. Shields in Madrid and Dr M. Naci Akkøk in Oslo. It aims to help families and educators prepare for the AI era with guidance, tools and structures that link school, home and work.',
			'Its Living with AI programme is a values-led AI literacy framework for Years 3 to 13, with lesson plans, teacher notes and parent materials. Other products include ParentBites, which links digital parenting to the school curriculum, and JanusEd for curriculum and skills planning.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Living with AI: AI literacy curriculum for Years 3 to 13', 'url' => 'https://crafting-tomorrow.com/solutions/living-with-ai/' ),
			array( 'theme' => 'safe', 'title' => 'ParentBites: digital parenting linked to the curriculum', 'url' => 'https://crafting-tomorrow.com/solutions/parentbites/' ),
			array( 'theme' => 'future', 'title' => 'Free webinars, podcasts and reading on AI', 'url' => 'https://crafting-tomorrow.com/resources/' ),
		),
	),
	'digitall' => array(
		'create'           => true,
		'title'            => 'DigitALL',
		'logo'             => 'digitall.png',
		'type'             => 'charity',
		'website'          => 'https://www.digitall.charity/',
		'intro'            => 'DigitALL is a charity helping young people build digital skills and leadership through coding, AI and climate-focused projects, with support for teachers.',
		'bio'              => array(
			'DigitALL is a charity that equips young people in schools and their local communities with the digital skills and leadership they need for current and future digital careers, while developing their digital wellbeing. Its active learning is set in the context of climate and sustainability.',
			'It inspires young people to become confident, discerning users and developers of technology, through AI, coding, data science, cybersecurity, engineering and internet safety. Spark AI covers inclusive and alternative AI for teachers and pupils, and EcoCode teaches coding through sustainability with the micro:bit.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Spark AI: inclusive and alternative AI for teachers and pupils', 'url' => 'https://www.digitall.charity/sparkai' ),
			array( 'theme' => 'creative', 'title' => 'EcoCode: sustainable computing with the micro:bit (free activity packs)', 'url' => 'https://www.digitall.charity/ecocode' ),
		),
		// Tiles for the homepage's "Extra Resources" section (featured resources), added once if their link is not there yet.
		'featured_resources' => array(
			array(
				'title'   => 'Spark AI',
				'org'     => 'DigitALL',
				'strand'  => 'smart',
				'url'     => 'https://www.digitall.charity/sparkai',
				'excerpt' => 'Free classroom resources and training on inclusive and alternative AI, including micro:bit AI and a KS3 AI and biodiversity unit.',
			),
			array(
				'title'   => 'EcoCode',
				'org'     => 'DigitALL',
				'strand'  => 'creative',
				'url'     => 'https://www.digitall.charity/ecocode',
				'excerpt' => 'Free micro:bit activity packs that teach coding and AI through sustainability, for ages 8 to 13.',
			),
		),
	),
	'e-act' => array(
		'title'            => 'E-ACT',
		'type'             => 'mat',
		'fix_type'         => true,
		'website'          => 'https://www.e-act.org.uk/',
		'intro'            => 'E-ACT is a national multi-academy trust running 37 primary and secondary academies across England, aiming to open minds and open doors for every pupil.',
		'bio'              => array(
			'E-ACT is a multi-academy trust formed in 2008. It runs 37 academies across England, serving around 25,000 pupils with about 3,500 staff, in areas including London, Bristol, Sheffield and Buckinghamshire.',
			'Its mission is to open minds and open doors for pupils, staff and communities. Its values are Think Big, Show Team Spirit and Doing the Right Thing. The trust shares best practice between its academies and also offers routes into teaching.',
		),
		'ai_resources_url' => '',
		'links'            => array(),
	),
	'education-links' => array(
		'title'            => 'Education Links',
		'type'             => 'alternative-provision',
		'website'          => 'https://www.education-links.org/',
		'intro'            => 'Education Links is an alternative provision free school in Newham, east London, giving young people aged 11 to 16 a personalised route back into education or training.',
		'bio'              => array(
			'Education Links is an alternative provision and SEND free school in Newham, London, for pupils aged 11 to 16 referred by mainstream schools or the local authority. It is run by Community Links Academy Trust and works across three sites.',
			'The school promotes Education with Character, building teamwork, resilience, creativity and enterprise alongside core subjects, PSHE and life skills. It offers mentoring, careers education and online safety guidance for students and families, with the aim of returning pupils to mainstream education or further training.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'safe', 'title' => 'Online safety guidance for students and parents', 'url' => 'https://www.education-links.org/Online-Safety/' ),
			array( 'theme' => 'future', 'title' => 'Careers education at Education Links', 'url' => 'https://www.education-links.org/Information/Careers-Education/' ),
		),
	),
	'harris-federation' => array(
		'title'            => 'Harris Federation',
		'type'             => 'mat',
		'fix_type'         => true,
		'website'          => 'https://www.harrisfederation.org.uk/',
		'intro'            => 'Harris Federation is a London and Essex multi-academy trust whose computer science programme supports teaching of computing and safe, effective use of AI in schools.',
		'bio'              => array(
			'Harris Federation is an education charity led and run by teachers, which opened its first school in 1991. It runs 55 primary and secondary academies across London and Essex and trains thousands of teachers each year for its own schools and others.',
			'Its Turbocharging Teaching of Computer Science work aims to grow participation in computing and set out how AI should and should not be used in education. Partners include the DfE, BCS, STEM Learning, the National Centre for Computing Education and Microsoft TEALS.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Turbocharging the teaching of computer science', 'url' => 'https://www.harrisfederation.org.uk/818/turbocharging-teaching-of-computer-science' ),
			array( 'theme' => 'creative', 'title' => 'Harris students build AI tools with Microsoft', 'url' => 'https://www.harrisfederation.org.uk/50/harris-highlights/post/190/harris-students-build-ai-tools-with-rishi-sunak-and-microsoft-uk-ireland-ceo' ),
		),
	),
	'invictus-education-trust' => array(
		'title'            => 'Invictus Education Trust',
		'type'             => 'mat',
		'fix_type'         => true,
		'website'          => 'https://www.invictus.education/',
		'intro'            => 'Invictus Education Trust is a multi-academy trust of eight primary and secondary schools in the West Midlands, educating children from nursery through to sixth form.',
		'bio'              => array(
			'Invictus Education Trust is a family of eight schools educating children from nursery to sixth form. Its schools include The Crestwood School, Ellowes Hall Sports College, Kinver High School and Sixth Form, Leasowes High School, The Pedmore High School, Wombourne High School, Rufford Primary and Milking Bank Primary.',
			'The trust\'s values are Respect, Resilience and Relationships. Through the Invictus Institute it supports staff development, including training to teach, NPQs and support for early career teachers. Its head office is at Kinver High School.',
		),
		'ai_resources_url' => '',
		'links'            => array(),
	),
	'national-centre-for-computing' => array(
		'title'            => 'National Centre for Computing Education',
		'type'             => 'institute',
		'website'          => 'https://teachcomputing.org/',
		'intro'            => 'The National Centre for Computing Education supports computing teachers in England with free curriculum resources, CPD courses on AI and machine learning, and student events.',
		'bio'              => array(
			'The National Centre for Computing Education (NCCE) is funded by the Department for Education and powered by STEM Learning. It supports primary and secondary teachers in England to teach computing through courses, certificates and a full set of classroom resources.',
			'Its AI offer includes a free self-paced online course, Introduction to Machine Learning and AI, plus CPD on AI in primary computing, Key Stage 3 and GCSE ethics. The free Teach Computing Curriculum covers AI at Key Stage 3, and student events connect pupils with industry experts.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Introduction to Machine Learning and AI course (free)', 'url' => 'https://teachcomputing.org/courses/CO231/introduction-to-machine-learning-and-ai' ),
			array( 'theme' => 'responsible', 'title' => 'AI and Ethics in GCSE computer science CPD', 'url' => 'https://teachcomputing.org/courses/CO427/ai-and-ethics-in-gcse-computer-science' ),
			array( 'theme' => 'creative', 'title' => 'Teach Computing Curriculum (free)', 'url' => 'https://teachcomputing.org/curriculum' ),
		),
	),
	'parent-zone' => array(
		'title'            => 'Parent Zone',
		'type'             => 'charity',
		'website'          => 'https://parentzone.org.uk/',
		'intro'            => 'Parent Zone is a UK social enterprise supporting families and schools in the digital age, with free online safety assemblies, media literacy lessons and Experience AI teacher training.',
		'bio'              => array(
			'Parent Zone is a UK social enterprise focused on children\'s digital lives. It researches the effects of technology on family life, advises government on new legislation, and creates family-friendly media literacy services, guides and training for parents, professionals and young people.',
			'For schools it runs Be Internet Legends, with free live online safety events for primary schools, and Be Internet Citizens, free media literacy lessons for secondary pupils. It also delivers free Experience AI training to help secondary teachers across subjects teach AI literacy.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Experience AI training for secondary teachers (free)', 'url' => 'https://parentzone.org.uk/ExperienceAI' ),
			array( 'theme' => 'safe', 'title' => 'Be Internet Legends live events for primary schools (free)', 'url' => 'https://parentzone.org.uk/beinternetlegends/assemblies' ),
			array( 'theme' => 'responsible', 'title' => 'Be Internet Citizens media literacy lessons (free)', 'url' => 'https://parentzone.org.uk/BeInternetCitizens/Resources' ),
		),
	),
	'pearson' => array(
		'title'            => 'Pearson',
		'type'             => 'publisher',
		'website'          => 'https://www.pearson.com/en-gb.html',
		'intro'            => 'Pearson is an education publisher and awarding body offering UK schools AI literacy support, research into AI in classrooms and an Extended Project Qualification pathway on AI.',
		'bio'              => array(
			'Pearson is an education company that publishes learning resources and awards qualifications including Edexcel and BTEC. In the UK it works with primary and secondary schools and colleges, and publishes an annual School and College Report on how teachers and students experience education, including their use of AI.',
			'Its Edexcel Project Qualification has an AI topic pathway that lets sixth form students explore generative AI tools and their ethical and social questions. Pearson also provides guidance for teachers and students on using AI responsibly in coursework, based on JCQ guidance.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'responsible', 'title' => 'Project Qualification AI pathway and teacher guidance', 'url' => 'https://qualifications.pearson.com/en/qualifications/edexcel-project-qualification/topic-pathways/artificial-intelligence-ai.html' ),
			array( 'theme' => 'smart', 'title' => 'Supporting AI literacy in schools: AI Awareness Day blog', 'url' => 'https://www.pearson.com/en-gb/schools/insights-and-events/schools-blog/2026/05/ai-awareness-day--supporting-ai-literacy-in-schools.html' ),
			array( 'theme' => 'future', 'title' => 'Pearson School and College Report 2026', 'url' => 'https://www.pearson.com/en-gb/schools/insights-and-events/topics/school-and-college-report/2026.html' ),
		),
	),
	'rapberry-pi-foundation' => array(
		'title'            => 'Raspberry Pi Foundation',
		'fix_title'        => true,
		'type'             => 'charity',
		'website'          => 'https://www.raspberrypi.org/',
		'intro'            => 'The Raspberry Pi Foundation is an education charity offering free computing and AI resources, including Experience AI lessons with Google DeepMind and online courses for teachers.',
		'bio'              => array(
			'The Raspberry Pi Foundation is a UK charity that helps young people learn computing and digital making. It provides free curriculum resources, online coding projects, teacher training and programmes such as Code Club, Coolest Projects and Astro Pi, along with research and the Hello World magazine for educators.',
			'Its AI education work includes Experience AI, developed with Google DeepMind, which offers free lessons, slide decks, worksheets and videos for secondary pupils. Teachers can also take free online courses on AI and join monthly research seminars on teaching AI across subjects.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Experience AI lessons with Google DeepMind (free)', 'url' => 'https://experience-ai.org/en/units' ),
			array( 'theme' => 'smart', 'title' => 'Introducing AI online course for educators', 'url' => 'https://training-hub.raspberrypi.org/en/courses/introducing-ai' ),
			array( 'theme' => 'creative', 'title' => 'AI coding projects pathway', 'url' => 'https://projects.raspberrypi.org/en/pathways/ai' ),
		),
	),
	'rm' => array(
		'title'            => 'RM',
		'type'             => 'edtech',
		'website'          => 'https://www.rm.com/',
		'intro'            => 'RM is a UK education technology company providing IT services, connectivity, web filtering and staff training to schools and trusts, including training on AI in education.',
		'bio'              => array(
			'RM plc is a UK company supplying technology and assessment services to education. Its RM Technology division provides IT support, broadband, devices, networking and software to primary and secondary schools, multi-academy trusts, independent schools and further education colleges, and says it supports over 6,000 schools.',
			'Its products include RM SafetyNet web filtering and RM Unify single sign-on. RM offers EdTech training for school staff covering current trends such as AI in education, and publishes blog guidance on safeguarding topics such as KCSIE. RM Assessment develops AI marking and learner feedback tools.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'safe', 'title' => 'Keeping schools safe: RM online safety overview', 'url' => 'https://www.rm.com/technology/empower-your-schools-safety' ),
			array( 'theme' => 'safe', 'title' => 'A quick guide to KCSIE 2026', 'url' => 'https://www.rm.com/technology/blog/a-quick-guide-to-kcsie-2026' ),
			array( 'theme' => 'future', 'title' => 'EdTech and technical training for school staff', 'url' => 'https://www.rm.com/technology/services/training' ),
		),
	),
	'run-dont-walk' => array(
		'title'            => 'Run Don’t Walk',
		'type'             => 'teacher',
		'website'          => 'https://rundontwalk.co.uk/',
		'intro'            => 'Run Don\'t Walk is primary computing specialist Phil Wickins\'s site, offering a free five-lesson AI unit for ages 8 to 14 plus training and CPD for schools.',
		'bio'              => array(
			'Run Don\'t Walk is the website of Phil Wickins, an independent primary computing specialist who coaches and trains teachers and subject leaders in UK and international schools. He is a Senior Professional Development Leader for STEM and a former NCCE National Specialist for Primary Computing Leaders.',
			'The site offers a free, five-lesson AI unit for Key Stages 2 and 3, covering how machines learn, bias, misinformation, safe use and designing an ethical AI system, with a Welsh version. It also shares blog guidance, physical computing ideas and paid CPD and INSET sessions.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Artificial Intelligence unit for KS2 and KS3 (free)', 'url' => 'https://rundontwalk.co.uk/artificial-intelligence/' ),
			array( 'theme' => 'safe', 'title' => 'Communicating with parents about AI learning', 'url' => 'https://rundontwalk.co.uk/2026/09/24/communicating-with-parents-about-ai-learning/' ),
			array( 'theme' => 'future', 'title' => 'What does the future of work actually look like?', 'url' => 'https://rundontwalk.co.uk/2026/09/30/what-does-the-future-of-work-actually-look-like/' ),
		),
	),
	'stem-learning' => array(
		'title'            => 'STEM Learning',
		'type'             => 'institute',
		'website'          => 'https://www.stem.org.uk/',
		'intro'            => 'STEM Learning supports UK teachers with professional development and classroom resources, including courses, webinars and activities on artificial intelligence for primary and secondary schools.',
		'bio'              => array(
			'STEM Learning provides professional development and resources for teachers of science, technology, engineering and maths across the UK. Its online library holds thousands of quality-assured teaching resources that teachers can use free of charge.',
			'Its artificial intelligence offer includes live-online courses on using AI in the classroom, short webinars on topics such as assessment and ethics, a self-paced introduction to machine learning for computing teachers, and unplugged AI activities for pupils.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Artificial Intelligence (AI) workshop activities', 'url' => 'https://www.stem.org.uk/resources/elibrary/resource/537337/artificial-intelligence-ai-workshop-activities' ),
			array( 'theme' => 'smart', 'title' => 'Introduction to Machine Learning and AI (teacher course)', 'url' => 'https://www.stem.org.uk/cpd/ondemand/527933/introduction-machine-learning-and-ai' ),
			array( 'theme' => 'creative', 'title' => 'STEM Clubs Artificial Intelligence resource', 'url' => 'https://www.stem.org.uk/resources/library/resource/534322/stem-clubs-artificial-intelligence-resource' ),
		),
	),
	'tech-london-advocates' => array(
		'title'            => 'Tech London Advocates',
		'type'             => 'tech-network',
		'fix_type'         => true,
		'website'          => 'https://www.techlondonadvocates.org.uk/',
		'intro'            => 'Tech London Advocates is a volunteer-led network of tech leaders whose Education working group connects schools, young people and employers with digital skills and career pathways.',
		'bio'              => array(
			'Tech London Advocates is a volunteer-led, independent and not-for-profit community of tech leaders, experts and investors. It began in London in 2013 and is now part of the wider Global Tech Advocates network, with dozens of working groups.',
			'Its Education working group aims to join up the education sector around skills and talent, apprenticeships and education technology. It runs a resource hub for teachers, students, young people and businesses, and highlights pathways into tech careers.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'future', 'title' => 'TLA Education working group', 'url' => 'https://www.techlondonadvocates.org.uk/working-groups/working-groups-education/' ),
			array( 'theme' => 'future', 'title' => 'Future of Work working group', 'url' => 'https://www.techlondonadvocates.org.uk/working-groups/future-of-work/' ),
		),
	),
	'tech-she-can' => array(
		'title'            => 'Tech She Can',
		'type'             => 'charity',
		'website'          => 'https://techshecan.org/',
		'intro'            => 'Tech She Can is a UK charity whose Tech We Can programme gives schools free lessons, assemblies and resources on technology careers, including AI, for ages 5 to 18.',
		'bio'              => array(
			'Tech She Can is a registered charity that aims to bring technology careers to life for children, with a focus on girls and on reaching young people who need it most. Its work is funded by member organisations and supporters.',
			'Its Tech We Can programme offers free lesson packs, live and on-demand lessons, animated lessons, after-school clubs and teacher CPD, designed by teachers for ages 5 to 18. Volunteer industry Champions can also visit classrooms.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'AI in Everyday Life lesson (ages 11-14)', 'url' => 'https://techshecan.org/previous-assemblies/ai-in-everyday-life-ages-11-14' ),
			array( 'theme' => 'safe', 'title' => 'Tech for Online Safety lesson (ages 11-14)', 'url' => 'https://techshecan.org/previous-assemblies/tech-for-online-safety-ages-11-14' ),
			array( 'theme' => 'future', 'title' => 'Tech We Can careers resources for schools (free)', 'url' => 'https://techshecan.org/tech-we-can' ),
		),
	),
	'ukbt-institute' => array(
		'title'            => 'UKBT Institute',
		'type'             => 'institute',
		'website'          => 'https://ukblacktech.com/',
		'intro'            => 'The UKBT Institute is the research and innovation arm of UK Black Tech, connecting technologists, academics and communities and supporting diverse young talent into digital careers.',
		'bio'              => array(
			'UK Black Tech is an innovation group, launched in 2017, that aims to make the UK tech sector more ethnically diverse. The UKBT Institute is its learning, research and validation arm, bringing together technologists, academics, professionals and communities.',
			'Its education work includes the Peckham Digital Accelerator Zone, run with UAL Creative Computing Institute and Southwark Council, which offers young adults training in web development and data science. It has also produced online courses with partners including FutureLearn and Tech Mums.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'future', 'title' => 'Peckham Digital Accelerator Zone (ages 18-30)', 'url' => 'https://ukblacktech.com/peckham-daz/' ),
			array( 'theme' => 'safe', 'title' => 'Cyber and Digital Trust campaign', 'url' => 'https://ukblacktech.com/campaigns/cyber-digital-trust/' ),
		),
	),
	'ultra-education-cic' => array(
		'title'            => 'Ultra Education CIC',
		'type'             => 'charity',
		'website'          => 'https://ultra.education/',
		'intro'            => 'Ultra Education CIC teaches entrepreneurship to young people aged 7 to 18 through school workshops, clubs and Abby, an AI mentor that coaches pupils through starting a business.',
		'bio'              => array(
			'Ultra Education CIC is a youth entrepreneurship organisation based in Wembley. It aims to create positive and lasting change for young people facing disadvantage, especially those from communities the education system serves less well.',
			'It works with primary and secondary schools through assemblies, class takeovers, after-school clubs and teacher CPD. Its AI mentor, Abby, guides pupils through ideas, market research and pitching, with a free basic tier and paid school licences.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'future', 'title' => 'Abby, an AI entrepreneurship mentor for schools', 'url' => 'https://ultra.education/abby-for-schools-entrepreneurship-mentor/' ),
			array( 'theme' => 'future', 'title' => 'Youth entrepreneurship teaching resources (free)', 'url' => 'https://ultra.education/teaching-resources-entrepreneurship/' ),
			array( 'theme' => 'creative', 'title' => 'Entrepreneurship programmes for schools', 'url' => 'https://ultra.education/school/' ),
		),
	),
	'unthinkable' => array(
		'title'            => 'Unthinkable',
		'type'             => 'agency',
		'fix_type'         => true,
		'website'          => 'https://www.unthinkabledigital.co.uk/',
		'intro'            => 'Unthinkable is a UK digital consultancy that designs learning experiences and digital services with universities, cultural institutions and charities.',
		'bio'              => array(
			'Unthinkable is a UK digital consultancy, working since 2010 to help organisations spot digital opportunities, deliver projects and build the teams to grow new ideas. Its learning design work includes online courses and learning platforms for universities, the V&A Academy and the Royal College of Art.',
			'For Nominet it researched how young people can thrive in a digital society, working with educators, skills providers and a group of young advisers, and reported six recommendations on digital skills in schools.',
		),
		'ai_resources_url' => '',
		'links'            => array(
			array( 'theme' => 'future', 'title' => 'Nominet: how young people can build digital skills', 'url' => 'https://www.unthinkabledigital.co.uk/case-studies/nominet' ),
		),
	),
	'ailitkit' => array(
		'create'           => true,
		'title'            => 'AILitKit',
		'logo'             => 'ailitkit.png',
		'fix_logo'         => true,
		'type'             => 'edtech',
		'fix_type'         => true,
		'website'          => 'https://ailitkit.com/',
		'intro'            => 'AILitKit helps teachers bring AI literacy into the lessons they already teach, in any subject and year group, with an unplugged activity in every guide.',
		'bio'              => array(
			'AILitKit helps teachers bring AI literacy into the lessons they already teach, in any subject and year group. A teacher describes a lesson, and AILitKit returns a guide with four classroom activities: unplugged, plugged, discussion and creative. Each one comes with what to say to the class and questions to ask, and every activity is mapped to AI literacy frameworks used in the UK, US, Europe, the UAE and Australia.',
			'Every guide includes an unplugged activity that needs no devices, and pupils never need an account. AILitKit is built by IN&ED and is free to start at ailitkit.com.',
		),
		'ai_resources_url' => 'https://ailitkit.com/ai-literacy',
		'links'            => array(
			array( 'theme' => 'smart', 'title' => 'Where AI literacy fits in every subject (free, no sign-in)', 'url' => 'https://ailitkit.com/ai-literacy' ),
			array( 'theme' => 'smart', 'title' => 'Free stand-alone AI literacy lessons', 'url' => 'https://ailitkit.com/resources' ),
			array( 'theme' => 'creative', 'title' => 'Create a guide for your own lesson (free account)', 'url' => 'https://ailitkit.com/' ),
		),
	),
);
