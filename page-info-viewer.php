<?php
/**
 * Plugin Name: Page Info Viewer
 * Description: <?php PageInfoViewer(); ?> で現在のページ情報とカスタムフィールドを表示します。
* Version: 1.0
* Author: YukiUota
*/

if ( ! defined( 'ABSPATH' ) ) exit;

function PageInfoViewer() {
if ( ! current_user_can('manage_options') ) return; // 管理者のみ表示
global $post;

if ( ! $post ) {
echo '<div style="border:2px solid red;padding:10px;background:#fff0f0;">';
    echo '⚠️ <strong>PageInfoViewer:</strong> 現在の投稿情報を取得できません。';
    echo '</div>';
return;
}

// 基本情報を取得
$info = [
'ID' => $post->ID,
'タイトル (get_the_title)' => get_the_title( $post->ID ),
'スラッグ (post_name)' => $post->post_name,
'公開状態 (post_status)' => $post->post_status,
'投稿タイプ (post_type)' => $post->post_type,
'テンプレート (get_page_template_slug)' => get_page_template_slug( $post->ID ) ?: '（デフォルト）',
'パーマリンク (get_permalink)' => get_permalink( $post->ID ),
'投稿日 (get_the_date)' => get_the_date( 'Y-m-d', $post->ID ),
'更新日 (get_the_modified_date)' => get_the_modified_date( 'Y-m-d', $post->ID ),
'抜粋 (get_the_excerpt)' => get_the_excerpt( $post->ID ),
];

// 出力開始
echo '<div style="
        background:#f8f9fa;
        border:2px dashed #0073aa;
        padding:15px;
        margin:20px 0;
        font-family:monospace;
        white-space:pre-wrap;
    ">';

    echo "🧩 Page Info Viewer\n";
    echo "───────────────────────────────\n";

    foreach ( $info as $label => $value ) {
    echo esc_html( $label ) . ': ' . esc_html( $value ) . "\n";
    }

    // カスタムフィールド取得
    $meta = get_post_meta( $post->ID );
    if ( ! empty( $meta ) ) {
    echo "\n--- カスタムフィールド ---\n";
    foreach ( $meta as $key => $values ) {
    // 配列の場合も対応
    $display_value = is_array( $values ) ? implode( ', ', $values ) : $values;
    echo esc_html( $key ) . ': ' . esc_html( $display_value ) . "\n";
    }
    } else {
    echo "\n--- カスタムフィールド ---\n(なし)\n";
    }

    echo "</div>";

// 読み込まれているアセットも表示
dev_print_loaded_assets();
}

/**
* 開発用:現在のリクエストで読み込まれている PHP ファイル、CSS、JS を取得して配列で返す
*
* @param array $args
* 'include_plugins' => bool - プラグインのファイルも表示するか(デフォルト false)
* 'only_theme' => bool - テーマ内のPHPファイルのみを表示(デフォルト true)
* @return array ['php'=>[], 'css'=>[], 'js'=>[]]
*/
function dev_get_loaded_assets( $args = array() ) {
$defaults = array(
'include_plugins' => false,
'only_theme' => true, // デフォルトをtrueに変更
);
$args = wp_parse_args( $args, $defaults );

// --- PHP ファイル ---
$included = get_included_files();
$theme_dir = get_template_directory();
$child_dir = is_child_theme() ? get_stylesheet_directory() : $theme_dir;

$php_files = array();
foreach ( $included as $file ) {
$short = $file;

// テーマ内のファイルのみを対象とする場合
if ( $args['only_theme'] ) {
// 子テーマのファイルをチェック
if ( strpos( $file, $child_dir ) === 0 ) {
$short = str_replace( $child_dir, '[child-theme]', $file );
$php_files[] = $short;
continue;
}
// 親テーマのファイルをチェック
if ( $theme_dir && strpos( $file, $theme_dir ) === 0 ) {
$short = str_replace( $theme_dir, '[theme]', $file );
$php_files[] = $short;
continue;
}
// テーマ外のファイルはスキップ
continue;
}

// 全てのファイルを対象とする場合(従来の動作)
if ( strpos( $file, $child_dir ) === 0 ) {
$short = str_replace( $child_dir, '[child-theme]', $file );
$php_files[] = $short;
continue;
}
if ( $theme_dir && strpos( $file, $theme_dir ) === 0 ) {
$short = str_replace( $theme_dir, '[theme]', $file );
$php_files[] = $short;
continue;
}

if ( $args['include_plugins'] ) {
$wp_plugin_dir = WP_PLUGIN_DIR;
if ( strpos( $file, $wp_plugin_dir ) === 0 ) {
$short = str_replace( $wp_plugin_dir, '[plugin]', $file );
$php_files[] = $short;
continue;
}
}

$php_files[] = $short;
}

// CSS / JS
global $wp_styles, $wp_scripts;
$css = array();
$js = array();

if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
foreach ( $wp_styles->queue as $handle ) {
if ( empty( $wp_styles->registered[ $handle ] ) ) continue;
$obj = $wp_styles->registered[ $handle ];
$src = $obj->src;

if ( $src && strpos( $src, 'http' ) !== 0 && strpos( $src, '//' ) !== 0 ) {
$src = $wp_styles->base_url . $src;
}
$src = set_url_scheme( $src );

$css[] = array(
'handle' => $handle,
'src' => $src,
'deps' => $obj->deps,
'ver' => $obj->ver,
'args' => $obj->args,
);
}
}

if ( isset( $wp_scripts ) && is_object( $wp_scripts ) ) {
foreach ( $wp_scripts->queue as $handle ) {
if ( empty( $wp_scripts->registered[ $handle ] ) ) continue;
$obj = $wp_scripts->registered[ $handle ];
$src = $obj->src;

if ( $src && strpos( $src, 'http' ) !== 0 && strpos( $src, '//' ) !== 0 ) {
$src = $wp_scripts->base_url . $src;
}
$src = set_url_scheme( $src );

$js[] = array(
'handle' => $handle,
'src' => $src,
'deps' => $obj->deps,
'ver' => $obj->ver,
'in_footer' => $obj->extra && ! empty( $obj->extra['group'] ) && $obj->extra['group'] === 1,
);
}
}

return array(
'php' => array_values( array_unique( $php_files ) ),
'css' => $css,
'js' => $js,
);
}

/**
* 開発用:HTML で見やすく出力するテンプレートタグ
*/
function dev_print_loaded_assets( $args = array() ) {
if ( ! current_user_can( 'edit_theme_options' ) ) {
return;
}

$assets = dev_get_loaded_assets( $args );

echo '<style>
.dev-loaded-assets {
    font-family: system-ui, "Noto Sans JP", sans-serif;
    font-size: 14px;
    background: #fff;
    border: 1px solid #ddd;
    padding: 12px;
    margin: 10px 0;
    border-radius: 6px;
}

.dev-loaded-assets h3 {
    margin: 50px 0 8px;
    font-size: 15px;
}

.dev-loaded-assets pre {
    white-space: pre-wrap;
    word-break: break-all;
    background: #f8f8f8;
    padding: 8px;
    border-radius: 4px;
}

.dev-loaded-assets table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
}

.dev-loaded-assets th,
.dev-loaded-assets td {
    border-bottom: 1px solid #eee;
    padding: 6px;
    text-align: left;
    font-size: 13px;
}
</style>';

echo '<div class="dev-loaded-assets">';
    echo '<h3>読み込まれているテーマ内の PHP ファイル</h3>';
    if ( empty( $assets['php'] ) ) {
    echo '<div>なし</div>';
    } else {
    echo '
    <pre>';
        foreach ( $assets['php'] as $f ) {
            echo esc_html( $f ) . "\n";
        }
        echo '</pre>';
    }

    echo '<h3>読み込まれている CSS</h3>';
    if ( empty( $assets['css'] ) ) {
    echo '<div>なし</div>';
    } else {
    echo '<table>
        <thead>
            <tr>
                <th>handle</th>
                <th>src</th>
                <th>ver</th>
                <th>deps</th>
            </tr>
        </thead>
        <tbody>';
            foreach ( $assets['css'] as $c ) {
            echo '<tr>';
                echo '<td>' . esc_html( $c['handle'] ) . '</td>';
                echo '<td><code>' . esc_html( $c['src'] ) . '</code></td>';
                echo '<td>' . esc_html( (string) $c['ver'] ) . '</td>';
                echo '<td>' . esc_html( implode( ', ', (array) $c['deps'] ) ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>
    </table>';
    }

    echo '<h3>読み込まれている JS</h3>';
    if ( empty( $assets['js'] ) ) {
    echo '<div>なし</div>';
    } else {
    echo '<table>
        <thead>
            <tr>
                <th>handle</th>
                <th>src</th>
                <th>ver</th>
                <th>deps</th>
                <th>footer</th>
            </tr>
        </thead>
        <tbody>';
            foreach ( $assets['js'] as $j ) {
            echo '<tr>';
                echo '<td>' . esc_html( $j['handle'] ) . '</td>';
                echo '<td><code>' . esc_html( $j['src'] ) . '</code></td>';
                echo '<td>' . esc_html( (string) $j['ver'] ) . '</td>';
                echo '<td>' . esc_html( implode( ', ', (array) $j['deps'] ) ) . '</td>';
                echo '<td>' . ( $j['in_footer'] ? 'yes' : 'no' ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>
    </table>';
    }

    echo '
</div>';
}

/**
* ショートコード [show_loaded_assets]
*/
function dev_loaded_assets_shortcode( $atts ) {
$atts = shortcode_atts( array(
'include_plugins' => '0',
'only_theme' => '1', // デフォルトを1(true)に変更
), $atts, 'show_loaded_assets' );

if ( ! current_user_can( 'edit_theme_options' ) ) {
return '';
}

ob_start();
dev_print_loaded_assets( array(
'include_plugins' => (bool) intval( $atts['include_plugins'] ),
'only_theme' => (bool) intval( $atts['only_theme'] ),
) );
return ob_get_clean();
}
add_shortcode( 'show_loaded_assets', 'dev_loaded_assets_shortcode' );