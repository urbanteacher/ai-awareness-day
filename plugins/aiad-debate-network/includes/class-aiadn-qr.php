<?php
/**
 * A small QR code generator: byte mode, error correction level M, versions 1 to 10 (up to 213 bytes).
 * Enough for the front-door and certificate-check links. Output is an inline SVG, so no image files,
 * no external service and no library to keep up to date.
 *
 * Follows ISO/IEC 18004; the algorithm is the standard one (Reed-Solomon over GF(256), eight masks
 * scored by the four penalty rules). scripts/aiadn-qr-check.py decodes its output with a real QR reader.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_QR {

	/** Version => array( EC codewords per block, array( array( block count, data codewords per block ), ... ) ). */
	private const BLOCKS = array(
		1  => array( 10, array( array( 1, 16 ) ) ),
		2  => array( 16, array( array( 1, 28 ) ) ),
		3  => array( 26, array( array( 1, 44 ) ) ),
		4  => array( 18, array( array( 2, 32 ) ) ),
		5  => array( 24, array( array( 2, 43 ) ) ),
		6  => array( 16, array( array( 4, 27 ) ) ),
		7  => array( 18, array( array( 4, 31 ) ) ),
		8  => array( 22, array( array( 2, 38 ), array( 2, 39 ) ) ),
		9  => array( 22, array( array( 3, 36 ), array( 2, 37 ) ) ),
		10 => array( 26, array( array( 4, 43 ), array( 1, 44 ) ) ),
	);

	private const ALIGN = array(
		1  => array(),
		2  => array( 6, 18 ),
		3  => array( 6, 22 ),
		4  => array( 6, 26 ),
		5  => array( 6, 30 ),
		6  => array( 6, 34 ),
		7  => array( 6, 22, 38 ),
		8  => array( 6, 24, 42 ),
		9  => array( 6, 26, 46 ),
		10 => array( 6, 28, 50 ),
	);

	/** @var array<int,array<int,bool>> */
	private $modules = array();
	/** @var array<int,array<int,bool>> */
	private $is_function = array();
	/** @var int */
	private $size = 0;
	/** @var int */
	private $version = 1;

	/** The QR code for a text as an SVG string. */
	public static function svg( string $text, string $label = 'QR code', int $quiet = 4 ): string {
		$qr = self::encode( $text );
		$n  = $qr->size + 2 * $quiet;
		$d  = '';
		for ( $y = 0; $y < $qr->size; $y++ ) {
			for ( $x = 0; $x < $qr->size; $x++ ) {
				if ( $qr->modules[ $y ][ $x ] ) {
					$d .= 'M' . ( $x + $quiet ) . ',' . ( $y + $quiet ) . 'h1v1h-1z';
				}
			}
		}
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $n . ' ' . $n . '" role="img" aria-label="' . esc_attr( $label ) . '" shape-rendering="crispEdges"><rect width="' . $n . '" height="' . $n . '" fill="#ffffff"/><path d="' . $d . '" fill="#231F20"/></svg>';
	}

	/** The matrix as lines of 0 and 1, for testing. */
	public static function matrix_text( string $text ): string {
		$qr    = self::encode( $text );
		$lines = array();
		foreach ( $qr->modules as $row ) {
			$lines[] = implode( '', array_map( static fn( $m ) => $m ? '1' : '0', $row ) );
		}
		return implode( "\n", $lines );
	}

	public static function encode( string $text ): self {
		$bytes = array_values( unpack( 'C*', $text ) ?: array() );
		$len   = count( $bytes );
		$version = 0;
		for ( $v = 1; $v <= 10; $v++ ) {
			$capacity = self::data_codewords( $v ) * 8;
			$needed   = 4 + ( $v < 10 ? 8 : 16 ) + 8 * $len;
			if ( $needed <= $capacity ) {
				$version = $v;
				break;
			}
		}
		if ( 0 === $version ) {
			throw new InvalidArgumentException( 'Text is too long for this QR generator.' );
		}

		// Bits: mode (byte = 0100), length, data, terminator, padding.
		$bits = '0100' . str_pad( decbin( $len ), $version < 10 ? 8 : 16, '0', STR_PAD_LEFT );
		foreach ( $bytes as $b ) {
			$bits .= str_pad( decbin( $b ), 8, '0', STR_PAD_LEFT );
		}
		$capacity = self::data_codewords( $version ) * 8;
		$bits    .= str_repeat( '0', min( 4, $capacity - strlen( $bits ) ) );
		$bits    .= str_repeat( '0', ( 8 - strlen( $bits ) % 8 ) % 8 );
		$data     = array();
		for ( $i = 0; $i < strlen( $bits ); $i += 8 ) {
			$data[] = bindec( substr( $bits, $i, 8 ) );
		}
		for ( $pad = 0xEC; count( $data ) < self::data_codewords( $version ); $pad ^= 0xEC ^ 0x11 ) {
			$data[] = $pad;
		}

		$qr          = new self();
		$qr->version = $version;
		$qr->size    = 17 + 4 * $version;
		$qr->build( self::add_error_correction( $data, $version ) );
		return $qr;
	}

	private static function data_codewords( int $version ): int {
		$total = 0;
		foreach ( self::BLOCKS[ $version ][1] as $group ) {
			$total += $group[0] * $group[1];
		}
		return $total;
	}

	/** Split the data into blocks, add Reed-Solomon error correction to each, and interleave. */
	private static function add_error_correction( array $data, int $version ): array {
		list( $ec_len, $groups ) = self::BLOCKS[ $version ];
		$divisor = self::rs_divisor( $ec_len );
		$blocks  = array();
		$ecs     = array();
		$k       = 0;
		foreach ( $groups as $group ) {
			for ( $b = 0; $b < $group[0]; $b++ ) {
				$block    = array_slice( $data, $k, $group[1] );
				$k       += $group[1];
				$blocks[] = $block;
				$ecs[]    = self::rs_remainder( $block, $divisor );
			}
		}
		$out     = array();
		$max_len = max( array_map( 'count', $blocks ) );
		for ( $i = 0; $i < $max_len; $i++ ) {
			foreach ( $blocks as $block ) {
				if ( $i < count( $block ) ) {
					$out[] = $block[ $i ];
				}
			}
		}
		for ( $i = 0; $i < $ec_len; $i++ ) {
			foreach ( $ecs as $ec ) {
				$out[] = $ec[ $i ];
			}
		}
		return $out;
	}

	private static function gf_mul( int $x, int $y ): int {
		$z = 0;
		for ( $i = 7; $i >= 0; $i-- ) {
			$z = ( $z << 1 ) ^ ( ( $z >> 7 ) * 0x11D );
			$z ^= ( ( $y >> $i ) & 1 ) * $x;
		}
		return $z & 0xFF;
	}

	private static function rs_divisor( int $degree ): array {
		$result   = array_fill( 0, $degree, 0 );
		$result[ $degree - 1 ] = 1;
		$root     = 1;
		for ( $i = 0; $i < $degree; $i++ ) {
			for ( $j = 0; $j < $degree; $j++ ) {
				$result[ $j ] = self::gf_mul( $result[ $j ], $root );
				if ( $j + 1 < $degree ) {
					$result[ $j ] ^= $result[ $j + 1 ];
				}
			}
			$root = self::gf_mul( $root, 0x02 );
		}
		return $result;
	}

	private static function rs_remainder( array $data, array $divisor ): array {
		$result = array_fill( 0, count( $divisor ), 0 );
		foreach ( $data as $b ) {
			$factor = $b ^ array_shift( $result );
			$result[] = 0;
			foreach ( $divisor as $i => $coef ) {
				$result[ $i ] ^= self::gf_mul( $coef, $factor );
			}
		}
		return $result;
	}

	/* ---- drawing ------------------------------------------------------ */

	private function set_function( int $x, int $y, bool $dark ): void {
		$this->modules[ $y ][ $x ]     = $dark;
		$this->is_function[ $y ][ $x ] = true;
	}

	private function build( array $codewords ): void {
		$n = $this->size;
		$this->modules     = array_fill( 0, $n, array_fill( 0, $n, false ) );
		$this->is_function = array_fill( 0, $n, array_fill( 0, $n, false ) );

		for ( $i = 0; $i < $n; $i++ ) {
			$this->set_function( 6, $i, 0 === $i % 2 );
			$this->set_function( $i, 6, 0 === $i % 2 );
		}
		foreach ( array( array( 3, 3 ), array( $n - 4, 3 ), array( 3, $n - 4 ) ) as $c ) {
			for ( $dy = -4; $dy <= 4; $dy++ ) {
				for ( $dx = -4; $dx <= 4; $dx++ ) {
					$dist = max( abs( $dx ), abs( $dy ) );
					$x    = $c[0] + $dx;
					$y    = $c[1] + $dy;
					if ( $x >= 0 && $x < $n && $y >= 0 && $y < $n ) {
						$this->set_function( $x, $y, 2 !== $dist && 4 !== $dist );
					}
				}
			}
		}
		$pos  = self::ALIGN[ $this->version ];
		$last = count( $pos ) - 1;
		foreach ( $pos as $i => $px ) {
			foreach ( $pos as $j => $py ) {
				if ( ( 0 === $i && 0 === $j ) || ( 0 === $i && $j === $last ) || ( $i === $last && 0 === $j ) ) {
					continue;
				}
				for ( $dy = -2; $dy <= 2; $dy++ ) {
					for ( $dx = -2; $dx <= 2; $dx++ ) {
						$this->set_function( $px + $dx, $py + $dy, 1 !== max( abs( $dx ), abs( $dy ) ) );
					}
				}
			}
		}
		$this->draw_format( 0 ); // Reserves the format areas.
		$this->draw_version();

		// Place the data bits in the zigzag order.
		$total = count( $codewords ) * 8;
		$i     = 0;
		for ( $right = $n - 1; $right >= 1; $right -= 2 ) {
			if ( 6 === $right ) {
				$right = 5;
			}
			for ( $vert = 0; $vert < $n; $vert++ ) {
				for ( $j = 0; $j < 2; $j++ ) {
					$x  = $right - $j;
					$up = 0 === ( ( $right + 1 ) & 2 );
					$y  = $up ? $n - 1 - $vert : $vert;
					if ( ! $this->is_function[ $y ][ $x ] && $i < $total ) {
						$this->modules[ $y ][ $x ] = ( ( $codewords[ $i >> 3 ] >> ( 7 - ( $i & 7 ) ) ) & 1 ) === 1;
						++$i;
					}
				}
			}
		}

		// Choose the mask with the lowest penalty.
		$best      = 0;
		$best_pen  = PHP_INT_MAX;
		for ( $m = 0; $m < 8; $m++ ) {
			$this->apply_mask( $m );
			$this->draw_format( $m );
			$pen = $this->penalty();
			if ( $pen < $best_pen ) {
				$best     = $m;
				$best_pen = $pen;
			}
			$this->apply_mask( $m );
		}
		$this->apply_mask( $best );
		$this->draw_format( $best );
	}

	private function apply_mask( int $mask ): void {
		for ( $y = 0; $y < $this->size; $y++ ) {
			for ( $x = 0; $x < $this->size; $x++ ) {
				switch ( $mask ) {
					case 0:
						$invert = 0 === ( $x + $y ) % 2;
						break;
					case 1:
						$invert = 0 === $y % 2;
						break;
					case 2:
						$invert = 0 === $x % 3;
						break;
					case 3:
						$invert = 0 === ( $x + $y ) % 3;
						break;
					case 4:
						$invert = 0 === ( intdiv( $x, 3 ) + intdiv( $y, 2 ) ) % 2;
						break;
					case 5:
						$invert = 0 === $x * $y % 2 + $x * $y % 3;
						break;
					case 6:
						$invert = 0 === ( $x * $y % 2 + $x * $y % 3 ) % 2;
						break;
					default:
						$invert = 0 === ( ( $x + $y ) % 2 + $x * $y % 3 ) % 2;
				}
				if ( ! $this->is_function[ $y ][ $x ] && $invert ) {
					$this->modules[ $y ][ $x ] = ! $this->modules[ $y ][ $x ];
				}
			}
		}
	}

	/** Format information: error correction level M (bits 00) and the mask. */
	private function draw_format( int $mask ): void {
		$data = ( 0 << 3 ) | $mask;
		$rem  = $data;
		for ( $i = 0; $i < 10; $i++ ) {
			$rem = ( $rem << 1 ) ^ ( ( $rem >> 9 ) * 0x537 );
		}
		$bits = ( ( $data << 10 ) | $rem ) ^ 0x5412;
		$n    = $this->size;
		$bit  = static fn( int $i ): bool => ( ( $bits >> $i ) & 1 ) === 1;
		for ( $i = 0; $i <= 5; $i++ ) {
			$this->set_function( 8, $i, $bit( $i ) );
		}
		$this->set_function( 8, 7, $bit( 6 ) );
		$this->set_function( 8, 8, $bit( 7 ) );
		$this->set_function( 7, 8, $bit( 8 ) );
		for ( $i = 9; $i < 15; $i++ ) {
			$this->set_function( 14 - $i, 8, $bit( $i ) );
		}
		for ( $i = 0; $i < 8; $i++ ) {
			$this->set_function( $n - 1 - $i, 8, $bit( $i ) );
		}
		for ( $i = 8; $i < 15; $i++ ) {
			$this->set_function( 8, $n - 15 + $i, $bit( $i ) );
		}
		$this->set_function( 8, $n - 8, true );
	}

	private function draw_version(): void {
		if ( $this->version < 7 ) {
			return;
		}
		$rem = $this->version;
		for ( $i = 0; $i < 12; $i++ ) {
			$rem = ( $rem << 1 ) ^ ( ( $rem >> 11 ) * 0x1F25 );
		}
		$bits = ( $this->version << 12 ) | $rem;
		for ( $i = 0; $i < 18; $i++ ) {
			$dark = ( ( $bits >> $i ) & 1 ) === 1;
			$a    = $this->size - 11 + $i % 3;
			$b    = intdiv( $i, 3 );
			$this->set_function( $a, $b, $dark );
			$this->set_function( $b, $a, $dark );
		}
	}

	/** The four penalty rules from the spec. */
	private function penalty(): int {
		$n      = $this->size;
		$m      = $this->modules;
		$result = 0;
		// Rule 1: runs of five or more of one colour, along rows and columns.
		for ( $pass = 0; $pass < 2; $pass++ ) {
			for ( $a = 0; $a < $n; $a++ ) {
				$run = 1;
				for ( $b = 1; $b < $n; $b++ ) {
					$cur  = $pass ? $m[ $b ][ $a ] : $m[ $a ][ $b ];
					$prev = $pass ? $m[ $b - 1 ][ $a ] : $m[ $a ][ $b - 1 ];
					if ( $cur === $prev ) {
						++$run;
						if ( 5 === $run ) {
							$result += 3;
						} elseif ( $run > 5 ) {
							++$result;
						}
					} else {
						$run = 1;
					}
				}
			}
		}
		// Rule 2: 2x2 blocks of one colour.
		for ( $y = 0; $y < $n - 1; $y++ ) {
			for ( $x = 0; $x < $n - 1; $x++ ) {
				if ( $m[ $y ][ $x ] === $m[ $y ][ $x + 1 ] && $m[ $y ][ $x ] === $m[ $y + 1 ][ $x ] && $m[ $y ][ $x ] === $m[ $y + 1 ][ $x + 1 ] ) {
					$result += 3;
				}
			}
		}
		// Rule 3: finder-like patterns.
		$patterns = array( '10111010000', '00001011101' );
		for ( $pass = 0; $pass < 2; $pass++ ) {
			for ( $a = 0; $a < $n; $a++ ) {
				$line = '';
				for ( $b = 0; $b < $n; $b++ ) {
					$line .= ( $pass ? $m[ $b ][ $a ] : $m[ $a ][ $b ] ) ? '1' : '0';
				}
				foreach ( $patterns as $p ) {
					$pos = 0;
					while ( false !== ( $found = strpos( $line, $p, $pos ) ) ) {
						$result += 40;
						$pos     = $found + 1;
					}
				}
			}
		}
		// Rule 4: how far the dark proportion is from half.
		$dark = 0;
		foreach ( $m as $row ) {
			foreach ( $row as $v ) {
				$dark += $v ? 1 : 0;
			}
		}
		$total   = $n * $n;
		$result += 10 * ( (int) ceil( abs( $dark * 20 - $total * 10 ) / $total ) - 1 );
		return $result;
	}
}
