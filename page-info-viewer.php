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
}