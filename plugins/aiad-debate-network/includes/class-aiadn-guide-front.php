<?php
/**
 * /conversation/guide/  How to run a debate: the teacher instructions for each age pathway.
 *
 * The step-by-step version of the explainer videos, laid out like a resource's lesson plan: what to do in the weeks
 * before, the equipment, how to set up the room, who does what, the debate stage by stage on a running clock, what
 * happens afterwards, and how to run it online. One guide for each age: primary, secondary and post-16.
 *
 * Inside the portal, for signed-in school staff and judges, like the prep pack. Each guide is also a PDF to download,
 * made from this same page by scripts/build-debate-guides.sh and kept in downloads/, which the web server does not
 * serve directly: the PDF goes out through this view, after the same sign-in check.
 *
 * The timings come from AIADN_Format, so the guide, the prep pack and the scorecard never disagree. The format is a
 * draft for review by debate educators before national use.
 *
 * The advice draws on, and the Further reading section credits: the national curriculum (English and citizenship), the
 * awarding bodies' Spoken Language Endorsement rules, the DfE's Political impartiality in schools (March 2025), the
 * ESU Schools' Mace handbook 2025-26 (timing signals, the floor debate, summary speeches, the four skill sets), the
 * Oxford schools' debating guide (notes, the chair's duties, ways to rebut) and the Voice 21 Oracy Framework.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Guide_Front {

	/** Where the PDFs live, one per age pathway: debate-guide-primary.pdf and so on. */
	const DOWNLOADS = 'downloads/';

	/** Who may read the guide: school staff and judges, signed in. */
	const ROLES = array( 'lead', 'teacher', 'slt', 'judge' );

	/** The age pathways, with the years each covers and what to call the students. */
	const AGES = array(
		'primary'   => array( 'name' => 'Primary', 'years' => 'Mainly Years 5 and 6', 'who' => 'pupils' ),
		'secondary' => array( 'name' => 'Secondary', 'years' => 'Years 7 to 11', 'who' => 'students' ),
		'post16'    => array( 'name' => 'Post-16 and college', 'years' => 'Sixth form and college', 'who' => 'students' ),
	);

	public static function pdf_path( string $age ): string {
		return AIADN_PLUGIN_DIR . self::DOWNLOADS . 'debate-guide-' . $age . '.pdf';
	}

	public static function url( string $age, array $args = array() ): string {
		return AIADN_Front::url( 'guide', array_merge( array( 'age' => $age ), $args ) );
	}

	/* ------------------------------------------------------------------ */
	/* The view                                                            */
	/* ------------------------------------------------------------------ */

	public static function view_guide(): string {
		AIADN_Front::set_title( 'How to run a debate' );
		$session = AIADN_Auth::current();
		if ( ! $session || ! in_array( $session['role'], self::ROLES, true ) ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}

		$age = AIADN_Front::get( 'age' );
		if ( ! isset( self::AGES[ $age ] ) ) {
			// Open on the age of the school's latest debate, if it has one.
			$debates = ! empty( $session['school_id'] ) ? AIADN_Debates::for_school( (int) $session['school_id'] ) : array();
			$latest  = $debates ? (string) reset( $debates )['age_group'] : '';
			$age     = isset( self::AGES[ $latest ] ) ? $latest : 'secondary';
		}

		if ( 'pdf' === AIADN_Front::get( 'download' ) ) {
			self::send_pdf( $age );
		}

		$back = 'judge' === $session['role'] ? AIADN_Front::url( 'judge' ) : AIADN_Front::url( 'school' );
		$h    = '<p class="aiadn__small"><a href="' . esc_url( $back ) . '">&larr; ' . ( 'judge' === $session['role'] ? 'Your debates' : 'Your school' ) . '</a></p>';
		$h   .= '<h1>How to run a debate</h1>';

		// The age pathways, as tabs that are ordinary links, so each guide has its own address.
		$h .= '<nav class="aiadn__guide-ages aiadn__noprint" aria-label="Age group"><ul>';
		foreach ( self::AGES as $key => $a ) {
			$h .= '<li><a href="' . esc_url( self::url( $key ) ) . '"' . ( $key === $age ? ' aria-current="page"' : '' ) . '><strong>' . esc_html( $a['name'] ) . '</strong><span>' . esc_html( $a['years'] ) . '</span></a></li>';
		}
		$h .= '</ul></nav>';

		$h .= '<div class="aiadn__guide-actions aiadn__noprint">';
		if ( is_readable( self::pdf_path( $age ) ) ) {
			$h .= '<a class="aiadn__button" href="' . esc_url( self::url( $age, array( 'download' => 'pdf' ) ) ) . '">Download PDF</a>';
		}
		$h .= '<button class="aiadn__button aiadn__button--quiet" type="button" onclick="window.print()">Print this guide</button></div>';

		$h .= self::document( $age );
		return $h;
	}

	/** The PDF, to signed-in readers only. Exits. */
	private static function send_pdf( string $age ): void {
		$file = self::pdf_path( $age );
		if ( ! is_readable( $file ) ) {
			AIADN_Front::redirect( 'guide', array( 'age' => $age ) );
		}
		nocache_headers();
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="AIAD-how-to-run-a-debate-' . $age . '.pdf"' );
		header( 'Content-Length: ' . (string) filesize( $file ) );
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}

	/**
	 * The guide itself, without the portal's navigation: the page shows it, and the PDF is printed from it.
	 */
	public static function document( string $age ): string {
		$age = isset( self::AGES[ $age ] ) ? $age : 'secondary';
		$a   = self::AGES[ $age ];
		$who = $a['who'];

		$h  = '<div class="aiadn__guide" data-age="' . esc_attr( $age ) . '">';
		$h .= '<div class="aiadn__printonly aiadn__guide-cover">' . AIADN_Front::logo_html() . '<p class="aiadn__eyebrow">National AI Conversation</p><h2 class="aiadn__guide-title">How to run a debate</h2></div>';
		$h .= '<p class="aiadn__guide-lead">' . esc_html( $a['name'] ) . ': ' . esc_html( $a['years'] ) . '. Two schools, one motion, ' . (int) AIADN_Format::TOTAL_MINUTES . ' minutes. Everything you need to run the debate, from setting up the room to the final vote.</p>';

		$h .= '<dl class="aiadn__guide-facts">';
		foreach ( array(
			'Debate time' => AIADN_Format::TOTAL_MINUTES . ' minutes',
			'Age group'   => $a['name'],
			'Class size'  => 'About 30',
			'Where'       => 'In person or online',
			'Judged on'   => implode( ', ', AIADN_Scorecards::CRITERIA ),
		) as $label => $value ) {
			$h .= '<div><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
		}
		$h .= '</dl>';

		$sections = array(
			'before'    => array( 'Two to three weeks before', self::before( $age ) ),
			'equipment' => array( 'What you need', self::equipment( $age ) ),
			'room'      => array( 'Set up the room', self::room( $age ) ),
			'roles'     => array( 'Who does what', self::roles( $age ) ),
			'day'       => array( 'On the day, step by step', self::steps( $age ) ),
			'rebuttal'  => array( 'Rebuttal card', self::rebuttal( $age ) ),
			'speech'    => array( 'What a good speech sounds like', self::speech( $age ) ),
			'progress'  => array( 'How debating grows, Key Stage 2 to 5', self::progress( $age ) ),
			'fair'      => array( 'Keeping it fair', self::fair( $age ) ),
			'include'   => array( 'Including every student', self::inclusion() ),
			'after'     => array( 'After the debate', self::after( $age ) ),
			'online'    => array( 'If you debate online', self::online( $age ) ),
			'curriculum' => array( 'Curriculum links', self::curriculum( $age ) ),
			'reading'   => array( 'Further reading', self::reading() ),
		);

		$h .= '<nav class="aiadn__guide-toc aiadn__noprint" aria-label="In this guide"><p>In this guide</p><ol>';
		foreach ( $sections as $id => $s ) {
			$h .= '<li><a href="#guide-' . esc_attr( $id ) . '">' . esc_html( $s[0] ) . '</a></li>';
		}
		$h .= '</ol></nav>';

		foreach ( $sections as $id => $s ) {
			$h .= '<section class="aiadn__panel aiadn__guide-section" id="guide-' . esc_attr( $id ) . '" aria-labelledby="guide-' . esc_attr( $id ) . '-h">';
			$h .= '<h2 id="guide-' . esc_attr( $id ) . '-h">' . esc_html( $s[0] ) . '</h2>' . $s[1] . '</section>';
		}

		$h .= '<p class="aiadn__small">The running order is recommended, not compulsory: change the stages or timings to suit your ' . esc_html( $who ) . '. The format is a draft and will be reviewed by debate educators before national use.</p>';
		$h .= '</div>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* Sections                                                            */
	/* ------------------------------------------------------------------ */

	private static function before( string $age ): string {
		$before = AIADN_Format::before( $age );
		$who    = self::AGES[ $age ]['who'];

		$h  = '<ol class="aiadn__guide-checks">';
		$h .= '<li><strong>Agree the fixture.</strong> On the debate page: the date, in person or online, the theme, the motion and a judge. Tell your ' . esc_html( $who ) . ' which side your school argues.</li>';
		$h .= '<li><strong>Survey your own ' . esc_html( $who ) . '.</strong> ' . esc_html( $before['survey'] ) . ' Use the same questions as the other school, from your prep pack, so the results can be compared on the day. Do not record names.</li>';
		$h .= '<li><strong>Research in three groups.</strong> ' . esc_html( $before['research'] ) . '<ul>';
		foreach ( AIADN_Format::RESEARCH_GROUPS as $group => $ask ) {
			$h .= '<li><strong>' . esc_html( $group ) . ':</strong> ' . esc_html( $ask ) . '</li>';
		}
		$h .= '</ul></li>';
		$h .= '<li><strong>Check every fact twice.</strong> Use more than one source for each fact, and write down where it came from. Never rely on AI-generated content alone: it can be wrong and still sound certain.</li>';
		$h .= '<li><strong>Name your roles</strong> (see Who does what).</li>';
		$h .= '<li><strong>Prepare the speeches.</strong> Start with a 60-second brainstorm of everything that comes to mind, then pick one central idea that every point leads back to. List the arguments you expect from the other side. Plan each speech as an opening line that hooks the room, two or three points with evidence, and a strong last line.</li>';
		$notes = array(
			'primary'   => 'Speakers can write their speech on a card in big letters and read from it.',
			'secondary' => 'Key Stage 3 speakers use prompt cards: a few words for each point, not a script. By Key Stage 4, headings only.',
			'post16'    => 'Headings only: the opening line, a heading and the key evidence for each argument, and a separate rebuttal sheet with space left to write what the other side says. Speakers should not read a script.',
		);
		$h .= '<li><strong>Notes.</strong> ' . esc_html( $notes[ $age ] ) . '</li>';
		$h .= '<li><strong>Warm up in lessons.</strong> Short games, one for each part of speaking well:<ul>';
		$h .= '<li><strong>Say it with feeling</strong> (voice): say an ordinary sentence as if angry, excited or worried, and the group guesses the feeling.</li>';
		$h .= '<li><strong>Don&rsquo;t say the word</strong> (words): describe a word on a card, such as &ldquo;privacy&rdquo;, without saying it.</li>';
		$h .= '<li><strong>Because, but, so</strong> (reasons): round the circle, each person adds to a claim with &ldquo;because&rdquo;, &ldquo;but&rdquo; or &ldquo;so&rdquo;.</li>';
		$h .= '<li><strong>Say it back</strong> (listening): before giving a view, sum up what the last speaker said, until they agree you have it right.</li>';
		$h .= '</ul></li>';
		$h .= '<li><strong>Rehearse in debate conditions:</strong> standing up, against the timer, with someone asking questions or interrupting.</li>';
		$extra = array(
			'primary'   => 'Practise the corner vote in a lesson beforehand, so pupils know where to go.',
			'secondary' => 'Run one practice round in small groups with talking roles, Builder, Challenger and Summariser, and use the challenge card to test both sides.',
			'post16'    => 'Practise points of information: how to offer one, and how a speaker accepts or declines it. Have students note how reliable each source is.',
		);
		$h .= '<li><strong>Practise.</strong> ' . esc_html( $extra[ $age ] ) . '</li>';
		$h .= '</ol>';
		return $h;
	}

	private static function equipment( string $age ): string {
		$common = array(
			'The motion on the board, where everyone can read it',
			'A timer everyone can see, on the board or a large screen',
			'Timing cards for the chair: 30 seconds and Stop, held up with each knock (see On the day)',
			'A rebuttal card for each team, printed from this guide',
			'Two tables at the front, facing the audience, with chairs for each team',
			'Each school\'s survey chart: one slide or one printed sheet',
			'Note-taking sheets and pens for the note-takers and the audience',
			'The judge\'s scorecard: on their phone, or printed from the debate page in case the Wi-Fi fails',
		);
		$by_age = array(
			'primary'   => array(
				'Three corner signs: For, Against and Not sure',
				'The sentence starter on display: “I think ___ because ___.”',
				'Fact cards, one fact to a card',
				'Sticky notes, for pupils to write floor questions on',
			),
			'secondary' => array(
				'The challenge card for the motion, printed',
				'Talking role cards: Builder, Challenger, Summariser',
				'The sources each team was given, with their notes',
			),
			'post16'    => array(
				'Research notes, with a note of how reliable each source is',
				'A phone or tablet poll for the vote, if you are not using a show of hands',
			),
		);
		$h  = '<div class="aiadn__guide-cols"><div><h3>For every debate</h3><ul class="aiadn__guide-ticks">';
		foreach ( $common as $item ) {
			$h .= '<li>' . esc_html( $item ) . '</li>';
		}
		$h .= '</ul></div><div><h3>For ' . esc_html( strtolower( self::AGES[ $age ]['name'] ) ) . '</h3><ul class="aiadn__guide-ticks">';
		foreach ( $by_age[ $age ] as $item ) {
			$h .= '<li>' . esc_html( $item ) . '</li>';
		}
		$h .= '</ul><p class="aiadn__small">Online? You also need a camera, a room microphone and a projector: see If you debate online.</p></div></div>';
		return $h;
	}

	private static function room( string $age ): string {
		$corners = 'primary' === $age;
		$h       = '<figure class="aiadn__guide-room">' . self::room_svg( $corners ) . '<figcaption>The room from above. School A, the Proposition, argues for; School B, the Opposition, argues against.</figcaption></figure>';
		$h      .= '<ol class="aiadn__guide-checks">';
		$h      .= '<li><strong>Front of the room:</strong> School A&rsquo;s table on one side and School B&rsquo;s on the other, both facing the audience. Speakers stand to speak.</li>';
		$h      .= '<li><strong>The chair</strong> sits between the two tables, with the timer on the board behind them.</li>';
		$h      .= '<li><strong>The judge</strong> sits facing the front, where they can see and hear both teams, slightly apart from the audience.</li>';
		$h      .= '<li><strong>The audience</strong> sits in rows or a horseshoe, mixed between the schools, with a clear path for questioners to stand.</li>';
		if ( $corners ) {
			$h .= '<li><strong>Corner signs:</strong> put For, Against and Not sure in three corners, with room for a third of the class in each.</li>';
		}
		$h .= '<li><strong>Check before anyone arrives:</strong> the motion and the timer are on the board, and the survey charts are ready to show.</li>';
		$h .= '</ol>';
		return $h;
	}

	/** A plan of the room, drawn simply enough to print in black and white. */
	private static function room_svg( bool $corners ): string {
		$s  = '<svg viewBox="0 0 640 380" role="img" aria-label="Room layout: the board at the front, the chair in the middle, School A and School B at tables either side, the judge facing them, and the audience behind." xmlns="http://www.w3.org/2000/svg" font-family="inherit" font-size="15">';
		$s .= '<rect x="2" y="2" width="636" height="376" rx="10" fill="none" stroke="currentColor" stroke-width="2"/>';
		$s .= '<rect x="200" y="14" width="240" height="30" rx="4" fill="currentColor" opacity=".12"/><text x="320" y="34" text-anchor="middle" font-weight="700">Board: motion and timer</text>';
		$s .= '<rect x="40" y="70" width="180" height="56" rx="6" class="aiadn__room-a"/><text x="130" y="94" text-anchor="middle" font-weight="700">School A</text><text x="130" y="114" text-anchor="middle" font-size="13">Proposition (for)</text>';
		$s .= '<rect x="420" y="70" width="180" height="56" rx="6" class="aiadn__room-b"/><text x="510" y="94" text-anchor="middle" font-weight="700">School B</text><text x="510" y="114" text-anchor="middle" font-size="13">Opposition (against)</text>';
		$s .= '<circle cx="320" cy="98" r="26" fill="none" stroke="currentColor" stroke-width="2"/><text x="320" y="103" text-anchor="middle" font-weight="700">Chair</text>';
		$s .= '<rect x="270" y="168" width="100" height="40" rx="6" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="5 4"/><text x="320" y="193" text-anchor="middle" font-weight="700">Judge</text>';
		for ( $row = 0; $row < 3; $row++ ) {
			for ( $col = 0; $col < 10; $col++ ) {
				$s .= '<circle cx="' . ( 140 + $col * 40 ) . '" cy="' . ( 240 + $row * 30 ) . '" r="9" fill="currentColor" opacity=".25"/>';
			}
		}
		$s .= '<text x="320" y="345" text-anchor="middle" font-weight="700">Audience: both schools, mixed</text>';
		if ( $corners ) {
			$s .= '<text x="20" y="364" font-size="13" font-weight="700">For</text><text x="620" y="364" text-anchor="end" font-size="13" font-weight="700">Against</text><text x="20" y="34" font-size="13" font-weight="700">Not sure</text>';
		}
		return $s . '</svg>';
	}

	private static function roles( string $age ): string {
		$h   = '<p>For a class of about 30. Each school names six roles. Everyone else is a note-taker or a questioner, so the whole room has a job.</p>';
		$h  .= '<table class="aiadn__table aiadn__guide-roles"><caption>Roles</caption><thead><tr><th scope="col">Role</th><th scope="col">What they do</th></tr></thead><tbody>';
		$h  .= '<tr><th scope="row">Chair (1, agreed between the schools)</th><td>Introduces the motion and the rules, keeps time, takes the floor questions and announces the votes. Often a teacher at primary; a student can chair at secondary and post-16.</td></tr>';
		$h  .= '<tr><th scope="row">Judge (1)</th><td>Invited by the host school. Scores each school from 1 to 5 on ' . esc_html( strtolower( implode( ', ', AIADN_Scorecards::CRITERIA ) ) ) . ', and submits the official result.</td></tr>';
		foreach ( AIADN_Format::ROLES as $role => $job ) {
			$h .= '<tr><th scope="row">' . esc_html( $role ) . '</th><td>' . esc_html( $job ) . '</td></tr>';
		}
		$h .= '</tbody></table>';
		$tip = array(
			'primary'   => 'Give every pupil a job card with their role on it. Speakers can read from a card; questioners write their question on a sticky note first.',
			'secondary' => 'Rotate the speaking roles between debates, so different students speak each time. Key Stage 3 classes can share a speech between two speakers.',
			'post16'    => 'Speakers may offer and take points of information during the opening cases. The lead note-taker tracks which points went unanswered, for the rebuttal.',
		);
		$h .= '<p class="aiadn__guide-tip"><strong>Teacher tip.</strong> ' . esc_html( $tip[ $age ] ) . '</p>';
		$order = 'primary' === $age
			? 'The chair keeps order with the quiet signal: for talking during a speech, interrupting, or anything unkind.'
			: 'The chair keeps order by saying &ldquo;Order!&rdquo;: for noise during a speech, interrupting, a speaker running on well past time, or anything discourteous.';
		$h .= '<p><strong>Keeping order.</strong> ' . $order . ' The judge says nothing during the debate: they listen, take notes and score.</p>';
		return $h;
	}

	/** The debate on a running clock, one step per stage, with what students do and a tip for each age. */
	private static function steps( string $age ): string {
		$who   = self::AGES[ $age ]['who'];
		$extra = self::stage_notes( $age );
		$h     = '<p>The first ' . (int) AIADN_Format::START_UP_MINUTES . ' minutes set the scene; the rest is the debate. The time beside each step is minutes from the start. Where a stage says &ldquo;each&rdquo;, each school has that long in turn.</p>';
		$h    .= '<div class="aiadn__guide-signals"><h3>Timing signals</h3>' . self::signals( $age ) . '</div>';
		$h    .= '<ol class="aiadn__guide-steps">';
		foreach ( AIADN_Format::run( $age ) as $i => $s ) {
			$note = $extra[ $s['key'] ] ?? array( '', '' );
			$h   .= '<li class="aiadn__guide-step aiadn__guide-step--' . esc_attr( $s['part'] ) . '">';
			$h   .= '<div class="aiadn__guide-rail"><span class="aiadn__guide-num">' . (int) ( $i + 1 ) . '</span><span class="aiadn__guide-clock">' . esc_html( AIADN_Format::clock( $s['start'] ) ) . '</span></div>';
			$h   .= '<div class="aiadn__guide-body"><h3>' . esc_html( $s['label'] ) . ' <span class="aiadn__guide-len">' . AIADN_Format::minutes_label( $s['minutes'], $s['each'] ) . '</span></h3>';
			$h   .= '<p>' . esc_html( $s['what'] ) . '</p>';
			if ( '' !== $note[0] ) {
				$h .= '<p class="aiadn__guide-who"><strong>' . esc_html( ucfirst( $who ) ) . ':</strong> ' . esc_html( $note[0] ) . '</p>';
			}
			if ( '' !== $note[1] ) {
				$h .= '<p class="aiadn__guide-tip"><strong>Teacher tip.</strong> ' . esc_html( $note[1] ) . '</p>';
			}
			$h .= '</div></li>';
		}
		$h .= '</ol>';
		return $h;
	}

	/**
	 * For each stage: what the students do, and a tip for the teacher.
	 *
	 * @return array<string,array{0:string,1:string}>
	 */
	private static function stage_notes( string $age ): array {
		$notes = array(
			'welcome'    => array(
				'Speakers sit at their school\'s table; everyone else sits in the audience.',
				'The chair says the one rule out loud: challenge the argument, respect the person.',
			),
			'open_vote'  => array(
				'Everyone votes for, against or undecided.',
				'Write the numbers on the board and leave them there: you need them for the swing at the end.',
			),
			'survey'     => array(
				'The two survey presenters from each school share their chart and one key finding.',
				'One chart is plenty. Keep it to what their own students said, not what they think.',
			),
			'opening'    => array(
				'The opening speaker makes the case. Note-takers on the other side write down every point to answer.',
				'One knock and the 30 seconds card when time is nearly up; two knocks and Stop at the end. Let them finish the sentence.',
			),
			'prep'       => array(
				'The note-takers tell the rebuttal speaker which points to answer. In the audience, pairs talk about the strongest point so far.',
				'The lead note-taker briefs the rebuttal speaker. Keep the timer running on the board.',
			),
			'rebuttal'   => array(
				'The rebuttal speaker answers what the other side actually said, using the notes.',
				'A rebuttal is not a second speech. Listen for “They said… but…”.',
			),
			'floor'      => array(
				'Questioners ask a question to either side; any speaker may answer. Nobody questions their own school\'s team.',
				'Take questions from both schools in turn, and keep each answer short. Or try it the competition way: take three or four questions in a row, and the teams answer them in their closing speeches.',
			),
			'closing'    => array(
				'The closing speaker sums up why their side should win the vote.',
				'No new arguments in the closing speech: pick the two or three biggest disagreements of the debate and show why your side won them.',
			),
			'buffer'     => array(
				'',
				'Use it to catch up if a stage ran over. If you are on time, give it to floor questions.',
			),
			'final_vote' => array(
				'Everyone votes again. The chair announces the swing: how many changed their minds.',
				'The vote shows who persuaded the room; the judge\'s scorecard is the official result.',
			),
		);
		$by_age = array(
			'primary'   => array(
				'welcome'   => array( 1 => 'Point to the sentence starter on the board, and agree a quiet signal, such as a raised hand.' ),
				'open_vote' => array( 0 => 'Pupils walk to the For, Against or Not sure corner, then tell a talk partner why they chose it.', 1 => 'Count each corner and write the numbers on the board, then everyone sits back down.' ),
				'opening'   => array( 1 => 'Speakers can read from their card. Stand beside a nervous speaker if it helps.' ),
				'floor'     => array( 1 => 'Pick questions from the sticky notes pupils wrote during the opening cases.' ),
				'final_vote' => array( 0 => 'Pupils go back to a corner. Who moved? Ask one or two pupils what changed their mind.' ),
			),
			'secondary' => array(
				'floor' => array( 1 => 'If questions dry up, read the challenge card to both sides.' ),
			),
			'post16'    => array(
				'floor'    => array( 0 => 'Take three or four questions in a row, from both schools. Teams do not answer straight away: they answer in their closing speeches. Nobody questions their own school\'s team.', 1 => 'Note each question on the board, so the closing speakers can see what they need to answer.' ),
				'closing'  => array( 0 => 'The closing speaker answers the floor questions and sums up the two or three biggest disagreements.', 1 => 'Nothing new, except to develop what was already said: in competitions, a brand-new argument in a closing speech loses marks.' ),
				'welcome'  => array( 1 => 'Explain points of information: stand and say “Point of information”; the speaker accepts or declines. None in the first or last 30 seconds of a speech.' ),
				'opening'  => array( 0 => 'The opening speaker makes the case, and may accept points of information from the other side.' ),
				'survey'   => array( 1 => 'Ask presenters to name one limit of their survey: sample size or leading wording.' ),
				'final_vote' => array( 0 => 'Everyone votes again, the same way as at the start, so the two votes can be compared.' ),
			),
		);
		foreach ( $by_age[ $age ] as $key => $change ) {
			foreach ( $change as $i => $text ) {
				$notes[ $key ][ $i ] = $text;
			}
		}
		return $notes;
	}

	/** Knocks on the table, with the card held up at the same time, so everyone hears or sees the time. */
	private static function signals( string $age ): string {
		if ( 'post16' === $age ) {
			$rows = array(
				'One knock after 30 seconds' => 'Points of information may now be offered.',
				'One knock 30 seconds before the end' => 'Last 30 seconds: no more points of information. Hold up the 30 seconds card.',
				'Two knocks' => 'Time is up: finish the sentence. Hold up Stop.',
				'Knocking that keeps going' => 'From 15 seconds over, until the speaker sits down.',
			);
			$after = 'The first and last 30 seconds of each opening case are protected: no points of information. A point of information lasts about 10 seconds, and the speaker may accept or decline it. Offering too many, so the speaker cannot get going, is unfair, but a team that never offers or accepts one is not listening.';
		} else {
			$rows = array(
				'One knock' => '30 seconds left. Hold up the 30 seconds card.',
				'Two knocks' => 'Time is up: finish the sentence. Hold up Stop.',
				'Knocking that keeps going' => 'From 15 seconds over, until the speaker sits down.',
			);
			$after = 'primary' === $age ? 'Practise the knocks in a lesson first, so nobody is startled.' : '';
		}
		$h = '<dl class="aiadn__guide-knocks">';
		foreach ( $rows as $signal => $meaning ) {
			$h .= '<div><dt>' . esc_html( $signal ) . '</dt><dd>' . esc_html( $meaning ) . '</dd></div>';
		}
		$h .= '</dl>';
		return $h . ( '' !== $after ? '<p class="aiadn__small">' . esc_html( $after ) . '</p>' : '' );
	}

	/** Ways to answer the other side, pitched for the age, as a card each team can print. */
	private static function rebuttal( string $age ): string {
		$cards = array(
			'primary'   => array(
				array( 'I disagree because…', 'Say which point you are answering, then give your reason.' ),
				array( 'That is not always true. For example…', 'Give one example that shows the point does not always work.' ),
				array( 'But what about…?', 'Name someone or something the other side forgot.' ),
			),
			'secondary' => array(
				array( 'Not true', 'The fact is wrong, out of date or has no source. “Where did that number come from?”' ),
				array( 'Doesn\'t follow', 'The reason does not lead to the conclusion. “Even if that is true, it does not mean…”' ),
				array( 'Doesn\'t matter', 'It is true, but small or beside the point. “That affects very few people, while…”' ),
				array( 'Key Stage 4: they contradict themselves', 'Two speakers on the same side said different things, or a speaker promised a point and never made it.' ),
			),
			'post16'    => array(
				array( 'Factually wrong', 'The claim is false, out of date or unsourced.' ),
				array( 'Wrong cause', 'It mistakes what causes what, or reads the evidence the wrong way round.' ),
				array( 'Doesn\'t follow', 'The conclusion does not follow from the reasons.' ),
				array( 'Morally flawed', 'It is true, but the cost to people outweighs the benefit.' ),
				array( 'Unimportant or irrelevant', 'True, but too small to decide the debate, or beside the point.' ),
				array( 'Contradiction', 'Two speakers on the same side disagree, or a promised argument never arrived.' ),
				array( 'Counter-rebuttal', 'When they answer your point, show why their answer fails, so your point still stands.' ),
			),
		);
		$h  = '<p>Rebuttal answers what the other side actually said. Take on their strongest argument, even if your answer is not perfect: ignoring it is worse.</p>';
		$h .= '<div class="aiadn__guide-card"><p class="aiadn__guide-card-title">Rebuttal card: ' . esc_html( self::AGES[ $age ]['name'] ) . '</p><dl>';
		foreach ( $cards[ $age ] as $c ) {
			$h .= '<div><dt>' . esc_html( $c[0] ) . '</dt><dd>' . esc_html( $c[1] ) . '</dd></div>';
		}
		$h .= '</dl></div>';
		return $h;
	}

	/** Success criteria students can check themselves against, and that the judge's feedback uses. */
	private static function speech( string $age ): string {
		if ( 'primary' === $age ) {
			$h  = '<p>Four questions for pupils to ask themselves, one for each part of speaking well. Put them on the board while speakers practise, and use them for feedback.</p>';
			$h .= '<table class="aiadn__table aiadn__guide-criteria"><caption>Success criteria</caption><thead><tr><th scope="col">Part</th><th scope="col">Ask yourself</th></tr></thead><tbody>';
			foreach ( array(
				'Voice and body'      => 'Can everyone hear me? Am I changing my voice and looking at the audience to make my point?',
				'Words'               => 'Am I choosing words my audience understands, and strong words that persuade?',
				'Reasons'             => 'Is my point clear, and have I given a reason and an example?',
				'Listening'           => 'Did I listen to the other side, and answer what they really said?',
			) as $part => $ask ) {
				$h .= '<tr><th scope="row">' . esc_html( $part ) . '</th><td>' . esc_html( $ask ) . '</td></tr>';
			}
			$h .= '</tbody></table>';
			$h .= '<p class="aiadn__small">After the Voice 21 Oracy Framework: physical, linguistic, cognitive, and social and emotional.</p>';
		} else {
			$h  = '<p>Four skill sets, as used by the English-Speaking Union to judge the Schools&rsquo; Mace. Use them as success criteria while students prepare, and for feedback afterwards. The right-hand column shows where each one counts on our judge&rsquo;s scorecard.</p>';
			$h .= '<table class="aiadn__table aiadn__guide-criteria"><caption>Success criteria</caption><thead><tr><th scope="col">Skill set</th><th scope="col">What it looks like</th><th scope="col">Our scorecard</th></tr></thead><tbody>';
			foreach ( array(
				array( 'Expression and delivery', 'Varies pitch, pace and pauses; eye contact and open body language; clear words; brief notes, not a script read aloud.', 'Delivery' ),
				array( 'Reasoning and evidence', 'A clear position from the start; each point backed by an example or fact, and why it matters; takes on the other side\'s view.', 'Argument, Evidence' ),
				array( 'Organisation and prioritisation', 'Signposts its points; a strong opening and ending; adapts when the debate changes; keeps to time.', 'Argument, Delivery' ),
				array( 'Listening and response', 'Answers questions directly, not by repeating the speech; responds to what was said; stays calm when challenged.', 'Rebuttal' ),
			) as $r ) {
				$h .= '<tr><th scope="row">' . esc_html( $r[0] ) . '</th><td>' . esc_html( $r[1] ) . '</td><td>' . esc_html( $r[2] ) . '</td></tr>';
			}
			$h .= '</tbody></table>';
			$h .= '<p class="aiadn__small">Persuasive techniques worth teaching: the rule of three, repetition of a key phrase, a rhetorical question, a metaphor that makes an idea easy to picture. A short story only helps if it links straight back to the argument.</p>';
		}
		return $h;
	}

	/** The same skills across the key stages, with this guide's own stage marked. */
	private static function progress( string $age ): string {
		$here = array(
			'primary'   => array( 'ks2' ),
			'secondary' => array( 'ks3', 'ks4' ),
			'post16'    => array( 'ks5' ),
		)[ $age ];
		$cols = array( 'ks2' => 'KS2', 'ks3' => 'KS3', 'ks4' => 'KS4', 'ks5' => 'KS5' );
		$rows = array(
			'Notes'            => array( 'Read from a card', 'Prompt cards', 'Headings only', 'Headings only, no script' ),
			'Rebuttal'         => array( '“I disagree because…”', 'Not true, doesn\'t follow, doesn\'t matter', 'Adds contradictions', 'The full list, and counter-rebuttal' ),
			'Interruptions'    => array( 'None', 'None', 'Points of information, optional', 'Points of information, with protected time' ),
			'Floor questions'  => array( 'Written on sticky notes first', 'Asked live, answered straight away', 'Asked live, or answered in closing', 'Answered in the closing speeches' ),
			'Feedback'         => array( 'Four “ask yourself” questions', 'ESU four skill sets', 'ESU four skill sets', 'ESU four skill sets' ),
		);
		$h  = '<p>Each stage builds on the one before. Your ' . esc_html( self::AGES[ $age ]['who'] ) . '&rsquo; stage is highlighted; move a confident group on to the next column.</p>';
		$h .= '<table class="aiadn__table aiadn__guide-progress"><caption>How debating grows</caption><thead><tr><th scope="col"></th>';
		foreach ( $cols as $key => $label ) {
			$h .= '<th scope="col"' . ( in_array( $key, $here, true ) ? ' class="is-here"' : '' ) . '>' . esc_html( $label ) . '</th>';
		}
		$h .= '</tr></thead><tbody>';
		foreach ( $rows as $row => $cells ) {
			$h .= '<tr><th scope="row">' . esc_html( $row ) . '</th>';
			$i  = 0;
			foreach ( $cols as $key => $label ) {
				$h .= '<td' . ( in_array( $key, $here, true ) ? ' class="is-here"' : '' ) . '>' . esc_html( $cells[ $i++ ] ) . '</td>';
			}
			$h .= '</tr>';
		}
		$h .= '</tbody></table>';
		return $h;
	}

	/** Balance and neutrality, after the DfE's guidance on political impartiality. */
	private static function fair( string $age ): string {
		$who = self::AGES[ $age ]['who'];
		$h   = '<ul class="aiadn__guide-ticks">';
		foreach ( array(
			'Sides are given, not chosen. Tell ' . $who . ' it is fine to argue a side they disagree with: that is the skill.',
			'Both sides get the same time and the same chance to answer.',
			'The chair and teachers stay neutral, and keep their own views on the motion to themselves.',
			'Some AI motions touch on political questions, such as who should make the rules. The debate format gives ' . $who . ' at least two genuinely different views, which is what the DfE asks for.',
			'The judge is a visitor: follow your school\'s usual visitor and safeguarding checks, and a teacher stays in the room.',
			'Challenge the argument, never the person. Personal remarks are stopped by the chair.',
		) as $item ) {
			$h .= '<li>' . esc_html( $item ) . '</li>';
		}
		$h .= '</ul><p class="aiadn__small">See the DfE&rsquo;s Political impartiality in schools (updated March 2025), in Further reading.</p>';
		return $h;
	}

	/** Every student has a way in, whatever their needs. */
	private static function inclusion(): string {
		$h = '<ul class="aiadn__guide-ticks">';
		foreach ( array(
			'Every role counts. Note-takers, questioners and survey presenters shape the debate as much as the speakers.',
			'Speakers may read from a card, share a speech between two, or use sign language or a communication aid. Oracy includes all of these.',
			'If a student stammers, give them time and never finish their sentences. Brief the judge: a stammer is never marked down under Delivery.',
			'Use the cards and the knocks together, so every student can see or hear the time.',
			'Give the motion and the questions early to anyone who needs more time to think, and keep sentence starters on display.',
			'Give students learning English a short list of key words for the motion, and let them prepare with a partner.',
			'Plan adjustments with your SENCO, as you would for any lesson.',
		) as $item ) {
			$h .= '<li>' . esc_html( $item ) . '</li>';
		}
		return $h . '</ul>';
	}

	/** Where the debate sits in the curriculum, and the GCSE endorsement for older students. */
	private static function curriculum( string $age ): string {
		$h = '';
		if ( 'primary' === $age ) {
			$h .= '<p><strong>English, spoken language, Years 1 to 6 (statutory).</strong> Pupils should be taught to &ldquo;participate in discussions, presentations, performances, role play, improvisations and debates&rdquo;, to &ldquo;articulate and justify answers, arguments and opinions&rdquo;, and to &ldquo;consider and evaluate different viewpoints, attending to and building on the contributions of others&rdquo;.</p>';
			$h .= '<p><strong>English, reading, Years 5 and 6.</strong> Pupils explain their understanding &ldquo;through formal presentations and debates, maintaining a focus on the topic and using notes where necessary&rdquo;, and &ldquo;provide reasoned justifications for their views&rdquo;.</p>';
		} elseif ( 'secondary' === $age ) {
			$h .= '<p><strong>English, Key Stage 3, spoken English.</strong> Pupils should be taught to speak confidently and effectively, including through &ldquo;participating in formal debates and structured discussions, summarising and/or building on what has been said&rdquo;.</p>';
			$h .= '<p><strong>Citizenship, Key Stages 3 and 4.</strong> Pupils should be &ldquo;equipped with the skills to think critically and debate political questions&rdquo;. At Key Stage 4 they learn to &ldquo;weigh up evidence, make persuasive arguments and substantiate their conclusions&rdquo;.</p>';
		} else {
			$h .= '<p>There is no national curriculum after 16, but the same skills run through A levels, T levels and further study: weighing evidence, building an argument and answering challenge. The Oracy Education Commission (2024) found strong support from business leaders and parents for more time on these skills in school.</p>';
		}
		if ( 'primary' !== $age ) {
			$h .= '<div class="aiadn__guide-tip"><p><strong>GCSE English Language: the Spoken Language Endorsement.</strong> A debate speech can be the formal presentation for the endorsement, which every exam board grades Pass, Merit or Distinction, separately from the GCSE. The topic must be agreed in advance, the speech prepared, and the student must answer questions and feedback, all in about ten minutes.' . ( 'post16' === $age ? ' Useful for students resitting GCSE English Language.' : '' ) . '</p>';
			$h .= '<p>An inter-school debate is excellent practice, but not the assessment itself: the boards need video of a sample of students, and these debates are never recorded. Run the assessed version in your own school, under your own recording policy, using the same motion.</p></div>';
		}
		$h .= '<p class="aiadn__small">Oracy is growing in importance: the Curriculum and Assessment Review (November 2025) recommended a national oracy framework, and new primary and secondary frameworks are planned to go with the revised national curriculum from September 2028.</p>';
		return $h;
	}

	private static function reading(): string {
		$h = '<ul class="aiadn__guide-reading">';
		foreach ( array(
			array( 'DfE: National curriculum in England, English programmes of study', 'https://www.gov.uk/government/publications/national-curriculum-in-england-english-programmes-of-study' ),
			array( 'DfE: Citizenship programmes of study, Key Stages 3 and 4', 'https://www.gov.uk/government/publications/national-curriculum-in-england-citizenship-programmes-of-study' ),
			array( 'DfE: Political impartiality in schools', 'https://www.gov.uk/government/publications/political-impartiality-in-schools' ),
			array( 'Awarding bodies: Spoken Language Endorsement for GCSE English Language (AQA)', 'https://filestore.aqa.org.uk/resources/english/AQA-8700-SL-ENDORSEMENT.PDF' ),
			array( 'English-Speaking Union: Schools\' Mace handbook', 'https://www.esu.org/competitions/schools-mace/' ),
			array( 'Voice 21: The Oracy Framework', 'https://voice21.org/' ),
			array( 'Oak National Academy: Participating in effective debates (Key Stage 3)', 'https://www.thenational.academy/teachers/programmes/english-secondary-ks3/units/participating-in-effective-debates/lessons' ),
		) as $r ) {
			$h .= '<li><a href="' . esc_url( $r[1] ) . '" target="_blank" rel="noopener">' . esc_html( $r[0] ) . '</a><span class="aiadn__printonly aiadn__guide-url">' . esc_html( $r[1] ) . '</span></li>';
		}
		return $h . '</ul>';
	}

	private static function after( string $age ): string {
		$who = self::AGES[ $age ]['who'];
		$h   = '<ol class="aiadn__guide-checks">';
		$h  .= '<li><strong>The judge submits the scorecard</strong> on their phone, or enters the paper one later. Their submission is the official result, and both schools see it on the debate page.</li>';
		$h  .= '<li><strong>Ask your ' . esc_html( $who ) . ' again.</strong> Run the survey after the debate with a class PIN, to see whether views changed.</li>';
		$h  .= '<li><strong>Talk about it.</strong> What was the strongest argument on the other side? Did anything change your mind?</li>';
		$h  .= '<li><strong>Be recognised.</strong> Taking part is recorded straight away. Two debates against two different schools earns the National AI Debate School certificate.</li>';
		$h  .= '</ol>';
		return $h;
	}

	private static function online( string $age ): string {
		$h  = '<p>The host school, School A, sets up the meeting on its own platform and adds the link to the debate page. The link is then on the debate page and in both calendar entries.</p>';
		$h .= '<div class="aiadn__guide-cols"><div><h3>Equipment</h3><ul class="aiadn__guide-ticks">';
		foreach ( array(
			'One computer in each room, with a camera that shows the speakers',
			'A room microphone or speakerphone, so the whole team can be heard',
			'A projector and speakers, so the room sees and hears the other school',
			'The timer shared on screen by the chair, so both rooms see it',
		) as $item ) {
			$h .= '<li>' . esc_html( $item ) . '</li>';
		}
		$h .= '</ul></div><div><h3>Running it</h3><ul class="aiadn__guide-ticks">';
		foreach ( array(
			'Test the link, sound and camera together 15 minutes before',
			'The host admits everyone from the waiting room and controls who joins',
			'Nobody records the debate, and the chat stays off',
			'Speakers come to the camera to speak; mute the room when it is not your turn',
			'Votes: hands up to the camera, counted in each room, or a poll in the meeting',
			'The judge joins the meeting and scores on their phone',
		) as $item ) {
			$h .= '<li>' . esc_html( $item ) . '</li>';
		}
		$h .= '</ul></div></div>';
		if ( 'primary' === $age ) {
			$h .= '<p class="aiadn__guide-tip"><strong>Teacher tip.</strong> Corner votes still work online: each room counts its own corners and the chair adds the two together.</p>';
		}
		return $h;
	}
}
