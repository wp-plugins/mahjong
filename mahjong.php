<?php
/*
Plugin Name: Mahjong
Plugin URI: http://wordpress.1000sei.com/mahjong/
Description: Mahjong hai plugin
Author: Hirofumi Ohta
Version: 1.4
Author URI: http://wordpress.1000sei.com
*/

// メモ（追加機能予定など）
// ドラに輝き
// 牌数チェック機能
// 画像サイズ指定は横向きのとか考えると無理っぽい。。
// 説明表示モード
// 言語国際化

	/*
	 * 麻雀クラス
	 */
	class Mahjong {
		const SHORT_CODE = 'hai';
		// 定数は一部しか使っていない。。
		const MANZU = 'm';
		const PINZU = 'p';
		const SOUZU = 's';
		const TON = 'T';
		const NAN = 'N';
		const SHA = 'S';
		const PEE = 'P';
		const HAKU = 'H';
		const HATU = 'F';
		const CHUN = 'C';
		const AKA = '%';
		const KAMICHA = '(';
		const TOIMEN = '^';
		const SHIMOCHA = ')';
		const PARTS_SPLIT = ' ';
		
		// 画像ディレクトリ
		private $str_image_url;
		// 麻雀牌の側面表示モード
		private $lst_side_visibles;
		
		/*
		 * コンストラクタ
		 */
		public function __construct() {
			// 画像ディレクトリ
			if (preg_match("/themes/", __FILE__)) {
				if (is_child_theme()) {
					// 画像ディレクトリ（子テーマでのローカルテスト用）
					$this->str_image_url = get_stylesheet_directory_uri() . "/mahjong/images/";
				} else {
					// 画像ディレクトリ（親テーマでのローカルテスト用）
					$this->str_image_url = get_template_directory_uri() . "/mahjong/images/";
				}
			} else {
				// 画像ディレクトリ（プラグイン用）
				$this->str_image_url = plugins_url() . "/mahjong/images/";
			}
			// 麻雀牌の側面表示モード
			$this->lst_side_visibles = array(
				"none" => array(
					"p" => null,	// ポン
					"c" => null,	// チー
					"m" => null,	// みんかん
					"a" => null,	// あんかん
					"k" => null,	// かかん
					"t" => null,	// ツモ
					"d" => null,	// ドラ
					false => null,	// その他
					"s" => null,	// 捨て牌
				),
				"top" => array(
					"p" => false,	// ポン
					"c" => false,	// チー
					"m" => false,	// みんかん
					"a" => false,	// あんかん
					"k" => false,	// かかん
					"t" => false,	// ツモ
					"d" => false,	// ドラ
					false => false,	// その他
					"s" => false,	// 捨て牌
				),
				"bottom" => array(
					"p" => true,	// ポン
					"c" => true,	// チー
					"m" => true,	// みんかん
					"a" => true,	// あんかん
					"k" => true,	// かかん
					"t" => true,	// ツモ
					"d" => true,	// ドラ
					false => true,	// その他
					"s" => true,	// 捨て牌
				),
				"default" => array(
					"p" => true,	// ポン
					"c" => true,	// チー
					"m" => true,	// みんかん
					"a" => true,	// あんかん
					"k" => true,	// かかん
					"t" => false,	// ツモ
					"d" => true,	// ドラ
					false => false,	// その他
					"s" => true,	// 捨て牌
				)
			);
		}
		/*
		 * toString
		 */
		public function __toString() {
		}


		/*
		 * 麻雀牌ショートコード
		 *  $atts 属性情報の配列
		 *  $code [hai][/hai]に囲まれた中の内容
		 */
		function hai_function($atts, $code = null) {
			extract(
				shortcode_atts(
					array(
						'show_code' => false,				// コードを表示するか（デバッグ時などに使用）
						'bgcolor' => '#006600',				// 背景色
						'reach_zyun' => '0',					// リーチした巡
						'image_set' => 'default_small',		// 画像セット
						'side_visible' => 'none',		// 麻雀牌の側面表示モード
						//'hai_width' => false,
						//'hai_height' => false
					), 
					$atts
				)
			);
			//return "(" . $reach_zyun . ")";
			$this->reach_zyun = $reach_zyun;
			$this->image_set = $image_set . '/';
			$this->side_visible = $side_visible;
			/*
			$this->hai_size = "";
			if ($hai_width !== false) {
				$this->hai_size .= " width='{$hai_width}' ";
			}
			if ($hai_height !== false) {
				$this->hai_size .= " height='{$hai_height}' ";
			}
			*/
			//return $this->str_image_url;
			//$this->bgcolor = $bgcolor;
			//return $this->bgcolor;
			
			//$code = "123%m5pTNSPHFC p)555m c456s m)8m8m8m8m a9m9m9m9m9m k(5555s tH d5p6s s12345678m";
			//$code = "123%m5pTNSPHFC p)555m c456s m)8m8m8m8m a9m9m9m9m9m k(5555s tH d5p6s";
			//$code = "s12345678m";
			// 前後に区切り文字が入ってたら消す
			//$code = preg_replace("/^[\s]*(.*)/", "$1", $code);
			//$code = preg_replace("/([^\s]*)[\s]*$/", "$1", $code);// ←おかしい正規表現のはずなのになぜか動く
			$code = trim($code);
			
			/*
			 * 入力チェック
			 */
			if ($code == null) {
				return "(E)手牌情報が入力されていません";
			}
			// 複数のブランクを一つにまとめる
			$code = preg_replace("/[\s]+/", " ", $code);
			// パーツごとに区切る
			$lst_content = explode(' ', $code);
			// 正規表現チェック
			for ($i = 0; $i < count($lst_content); $i++) {
				if (!preg_match("/^(([pmk]{1}[\(\^\)]{1}|[actds])?(([1-9%]+[mps]{1})|([TNSPHFC]+))+)+$/", $lst_content[$i])) {
					return "(E)手牌情報の文法が不正です: 位置 {$i}: " . $lst_content[$i];
				}
			}
			
			//return $lst_content[1];
			// 
			$str_tag_hai_sutehai = "";
			$str_tag_hai = "";
			for ($i = 0; $i < count($lst_content); $i++) {
				//$str_content_type = false;
				// デフォルトの鳴き対象
				$str_from = self::KAMICHA;// 上家

				// コンテンツの開始位置
				$i_start_core_content = 0;
				// コンテンツタイプ
				$str_content_type = substr($lst_content[$i], 0, 1);
				if (array_search($str_content_type, array("p", "c", "m", "a", "k", "t", "d", "s")) !== false) {
					/* 手牌以外の場合 */
					// コンテンツの開始位置
					$i_start_core_content = 1;
					// 誰から
					$str_tmp = substr($lst_content[$i], 1, 1);
					if (array_search($str_tmp, array(self::KAMICHA, self::TOIMEN, self::SHIMOCHA)) !== false) {
						/* 上家、対面、下家が指定されている場合 */
						// コンテンツの開始位置
						$i_start_core_content = 2;
						// 誰から
						$str_from = $str_tmp;
					}
				} else {
					// 手牌
					$str_content_type = false;
				}
				// 真コンテンツ
				$lst_content[$i] = substr($lst_content[$i], $i_start_core_content);

				// 牌文字列から牌配列にする
				$lst_hai = $this->explodeHai($lst_content[$i]);
				// 牌配列から牌タグにする
				if ($str_content_type === "s") {
					// 捨て牌
					$str_tag_hai_sutehai .= "<span class='sutehai'>";
					$str_tag_hai_sutehai .= $this->tag_hai($lst_hai, $str_content_type, $str_from);
					$str_tag_hai_sutehai .= "</span>";
				} else {
					// 手牌
					$str_tag_hai .= "<span class='parts'>";
					$str_tag_hai .= $this->tag_hai($lst_hai, $str_content_type, $str_from);
					$str_tag_hai .= "</span>";
				}
			}
			
			$str_tag_hai = $str_tag_hai_sutehai . "<br />" . $str_tag_hai;

			// 全体囲み
			$str_ret = "";
			$str_ret .= "<span class='mahjong'>";
			$str_ret .= "<span class='tehai' style='background-color:{$bgcolor}'>{$str_tag_hai}</span>";
			if ($show_code) {
				$str_ret .= "<br /><span class='code'>入力コード: {$code}</span>";
				$str_ret .= "<br /><span class='code'>属性: " . var_export($atts, true) . "</span>";
			}
			$str_ret .= "</span>";
			$str_ret .= "<br />";
			//
			return $str_ret;
			//return $lst_content[0] . tag_hai($lst_hai) . $lst_hai[3];
		}
		/*
		 * 牌文字列から牌配列にする
		 */
		function explodeHai($str_hais) {
			// 牌配列
			$lst_hai = array();
			// 牌種類
			$str_hai_type = false;
			// 後ろから一文字ずつ見ていく
			for ($i = strlen($str_hais) - 1; $i >= 0; $i--) {
				// 一文字抜き取る
				$str_char = substr($str_hais, $i, 1);
				if(preg_match("/[TNSPHFC]{1}/", $str_char)) {
					/* 字牌の場合 */
					// 牌配列に入れる
					array_unshift($lst_hai, $str_char);
					// 牌種類をリセット
					$str_hai_type = false;
				}
				if(preg_match("/[1-9\%]{1}/", $str_char)) {
					/* 数字の場合 */
					if ($str_hai_type == false) {
						// 文法エラー
					} else {
						// %はURLエスケープ
						if ($str_char == "%") {
							$str_char = "%25";
						}
						// 牌配列に入れる
						array_unshift($lst_hai, $str_char . $str_hai_type);
					}
				} else {
					/* 牌種類の場合 */
					// 牌種類を設定
					$str_hai_type = $str_char;
				}
			}
			//
			return $lst_hai;
		}
		/*
		 * 牌配列から牌タグにする
		 */
		function tag_hai($lst_hai, $str_content_type = false, $str_from = false) {
			// 麻雀牌の側面表示モード
			//wp_die(var_export($this->lst_side_visibles));
			$side_is_bottom = $this->lst_side_visibles[$this->side_visible][$str_content_type];
			//
			$str_img_tags = "";
			switch ($str_content_type) {
				case "p":
					/* ポン */
					//$str_img_tags = "(p{$str_from}{$str_img_tags})";
					// タグ化
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::KAMICHA);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::TOIMEN);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::SHIMOCHA);
					break;
				case "c":
					/* チー */
					//$str_img_tags = "(c{$str_from}{$str_img_tags})";
					// タグ化
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, true);
					$str_img_tags .= $this->tag_hai_one($lst_hai[1], $side_is_bottom);
					$str_img_tags .= $this->tag_hai_one($lst_hai[2], $side_is_bottom);
					break;
				case "m":
					/* 明かん */
					//$str_img_tags = "(m{$str_from}{$str_img_tags})";
					// タグ化
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::KAMICHA);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::TOIMEN);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::SHIMOCHA);
					break;
				case "a":
					/* 暗かん */
					//$str_img_tags = "(a{$str_from}{$str_img_tags})";
					// タグ化
					$str_img_tags .= $this->tag_hai_one("U", $side_is_bottom);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom);
					$str_img_tags .= $this->tag_hai_one("U", $side_is_bottom);
					break;
				case "k":
					/* 加かん */
					//$str_img_tags = "(k{$str_from}{$str_img_tags})";
					// タグ化
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::KAMICHA, true);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::TOIMEN, true);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom, $str_from == self::SHIMOCHA, true);
					break;
				case "t":
					/* ツモ */
					//$str_img_tags = "(t{$str_from}{$str_img_tags})";
					// タグ化
					$str_img_tags .= $this->tag_label_one($str_content_type);
					$str_img_tags .= $this->tag_hai_one($lst_hai[0], $side_is_bottom);
					break;
				case "d":
					/* ドラ */
					//$str_img_tags = "(t{$str_from}{$str_img_tags})";
					// タグ化
					$str_img_tags .= $this->tag_label_one($str_content_type);
					for ($i = 0; $i < count($lst_hai); $i++) {
						// タグ化
						$str_img_tags .= $this->tag_hai_one($lst_hai[$i], $side_is_bottom);
					}
					break;
				default:
					/* その他 */
					for ($i = 0; $i < count($lst_hai); $i++) {
						if ($str_content_type === "s") {
							$is_Yoko = false;
							// 捨て牌
							// ６つずつ並べる
							if ($i !== 0 && $i % 6 === 0) {
								$str_img_tags .= "<br />";
							}
							// リーチ
							if ($i + 1 == $this->reach_zyun) {
								$is_Yoko = true;
							}
							// タグ化
							$str_img_tags .= $this->tag_hai_one($lst_hai[$i], $side_is_bottom, $is_Yoko);
						} else {
							// その他（手牌）
							// タグ化
							$str_img_tags .= $this->tag_hai_one($lst_hai[$i], $side_is_bottom, false);
						}
					}
			}
			return $str_img_tags;
		}
		/*
		 * 一つの牌タグ
		 */
		function tag_hai_one($str_hai, $side_is_bottom = false, $is_Yoko = false, $is_tumi = false) {
			// 牌を横にするか
			if ($is_Yoko) {
				$str_from = "1";
			} else {
				$str_from = "0";
			}
			// 画像ディレクトリ
			//$this->str_image_url = "http://localhost/wordpress/wp-content/themes/0911_my_plugin_mahjong/make_hai/images/";
			$str_image_url = $this->str_image_url . $this->image_set;
			// タグ化
			$str_img_tag = "";
			if ($is_Yoko && $is_tumi) {
				// 明かん用積み上げ
				$str_img_tag .= "<span class='minkantumi'>";
				$str_img_tag .= "<img src='{$str_image_url}{$str_hai}{$str_from}.png' alt='" . __( 'tile', 'mahjong' ) . "' />";
				$str_img_tag .= "<br />";
				$str_img_tag .= "<img src='{$str_image_url}{$str_hai}{$str_from}.png' alt='" . __( 'tile', 'mahjong' ) . "' />";
				$str_img_tag .= "</span>";
			} else {
				// 通常
				//$str_img_tag .= "<img src='{$str_image_url}{$str_hai}{$str_from}.png'>";
				//$str_img_tag .= "<span class='hai_side' style=\"background:url('{$str_image_url}U0.png');\">";
				$str_img_tag .= "<img src='{$str_image_url}{$str_hai}{$str_from}.png' alt='" . __( 'tile', 'mahjong' ) . "' />";
				//$str_img_tag .= "</span>";
			}
			// 側面画像
			//if ($is_Yoko) {
			//	$str_img_tag = "<span class='hai_side' style=\"background:url('{$str_image_url}U0.png');\">" . $str_img_tag . "</span>";
			//} else {
			if ($side_is_bottom === true) {
				// 横になって見せている牌
				$str_yoko_shadow_css = "_bottom";
			} else if ($side_is_bottom === false) {
				// 立てて見せてない牌 
				$str_yoko_shadow_css = "_top";
			} else {
				// 側面を表示しない 
				$str_yoko_shadow_css = "_none";
			}
			$str_img_tag = "<span class='hai_side{$str_yoko_shadow_css}' style=\"background-image:url('{$str_image_url}U{$str_from}.png');\">" . $str_img_tag . "</span>";
			//
			return $str_img_tag;
		}
		/*
		 * 一つのラベルタグ
		 */
		function tag_label_one($str_content_type) {
			$str_image_url = $this->str_image_url . $this->image_set;
			if ($str_content_type == "t") { 
				// ツモ
				$str_img_tag = "<img src='{$str_image_url}_tumo0.png' alt='" . __( 'tile', 'mahjong' ) . "' />";
			} else {
				// ドラ
				$str_img_tag = "<img src='{$str_image_url}_dora0.png' alt='" . __( 'tile', 'mahjong' ) . "' />";
			}
			//
			return $str_img_tag;
		}


		/* 
		 * スタイルシートの表示
		 */
		function set_style(){
			//$str_image_url = $this->str_image_url . $this->image_set;
?>
<style type="text/css">
.mahjong .tehai {
	display: inline-block;
	line-height: 0px;
	padding:8px;
}
.mahjong .tehai .sutehai {
	display: inline-block;
	padding:4px;
}
.mahjong .tehai .parts {
	display: inline-block;
	padding:4px;
}
.mahjong .tehai .minkantumi {
	display: inline-block;
}
.mahjong .hai_side_none {
	display: inline-block;
}
.mahjong .hai_side_top {
	display: inline-block;
	padding-top:5px;
}
.mahjong .hai_side_bottom {
	display: inline-block;
	padding-bottom:5px;
	backgorund-repeat:no-repeat;
	background-position:bottom;
}
.mahjong .code {
	display: inline-block;
	padding:4px;
	line-height: 1em;
}
.mahjong img {
	padding:0 !important;
	margin:0 !important;
}
</style>
<?php
		}
		
		/*
		 * ショートコードのエスケープ
		 *  [hai]に限ったものではなく、汎用的に使える
		 */
		//function esc_hai_function($atts, $code = null) {
		//	return $code;
		//}


		/*
		 * 管理ページ
		 */
		//   basename(__FILE__)だとfunctions.phpがページのURLになる
		function add_menu_page_cb() {
			// トップレベルメニュー 
			add_menu_page(
				__( 'mahjong tile', 'mahjong' ), // メニューが有効になった時に表示されるHTMLのページタイトル用テキスト。 
				__( 'mahjong tile', 'mahjong' ), // 管理画面のメニュー上での表示名。 
				'edit_themes', // このメニューページを閲覧・使用するために最低限必要なユーザーレベルまたはユーザーの種類と権限 。管理能力名（edit_themes等）で指定。
				'my_menu_page', // メニューページのコンテンツを表示するPHPファイル。とマニュアルにあるが実態は単なるslug。 
				array($this, 'add_menu_page_html') // メニューページにコンテンツを表示する関数。
			);
		}
		function add_menu_page_html() {
?>
			<div class="wrap">
				<?php screen_icon('edit'); ?>
				<h2><?php _e( 'mahjong tile', 'mahjong' ) ?></h2>
				<?php _e( 'Now, there are no configration.', 'mahjong' ) ?><br />
				<a href="http://wordpress.1000sei.com/mahjong/">http://wordpress.1000sei.com/mahjong/</a><br />
				<a href="http://profiles.wordpress.org/hirofumi-ohta/">
				<img src="http://www.gravatar.com/avatar/aff27cd19b5ac6526a11c278d8bcd9ae" alt='<?php _e( 'pukkyu', 'mahjong' ) ?>' /><br />
				</a>
			</div>
<?php
		}

	}

// プラグイン用の国際化用ファイル（MOファイル）をロード
load_plugin_textdomain('mahjong', false, basename(dirname(__FILE__)).'/languages' );

$objMahjong = new Mahjong();

// スタイルの設定
add_action('wp_head', array($objMahjong, 'set_style'));

// ショートコードの設定
//add_shortcode("esc_hai", array($objMahjong, 'esc_hai_function'));
add_shortcode(Mahjong::SHORT_CODE, array($objMahjong, 'hai_function'));

if (is_admin()) {
	// 管理ページ
	add_action('admin_menu', array($objMahjong, 'add_menu_page_cb'));
}

?>