<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Page_Info_View {
    /**
     * ページ情報のレンダリング
     */
    public function render_page_info( $info, $meta ) {
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
    
        // カスタムフィールド
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

    /**
     * アセット情報のレンダリング
     */
    public function render_assets_info( $assets ) {
        echo '<div style="
            background:#f8f9fa;
            border:2px dashed #0073aa;
            padding:15px;
            margin:20px 0;
            font-family:monospace;
            white-space:pre-wrap;
        ">';

        echo "📦 Loaded Assets\n";
        echo "───────────────────────────────\n\n";

        // PHP ファイル
        echo "--- テーマ内の PHP ファイル ---\n";
        if ( empty( $assets['php'] ) ) {
            echo "(なし)\n";
        } else {
            foreach ( $assets['php'] as $f ) {
                echo esc_html( $f ) . "\n";
            }
        }

        // CSS
        echo "\n--- 読み込まれている CSS ---\n";
        if ( empty( $assets['css'] ) ) {
            echo "(なし)\n";
        } else {
            foreach ( $assets['css'] as $c ) {
                echo sprintf(
                    "[%s] %s (ver:%s, deps:%s)\n",
                    esc_html( $c['handle'] ),
                    esc_html( $c['src'] ),
                    esc_html( (string) $c['ver'] ),
                    esc_html( implode( ', ', (array) $c['deps'] ) ?: 'なし' )
                );
            }
        }

        // JS
        echo "\n--- 読み込まれている JS ---\n";
        if ( empty( $assets['js'] ) ) {
            echo "(なし)\n";
        } else {
            foreach ( $assets['js'] as $j ) {
                echo sprintf(
                    "[%s] %s (ver:%s, deps:%s, footer:%s)\n",
                    esc_html( $j['handle'] ),
                    esc_html( $j['src'] ),
                    esc_html( (string) $j['ver'] ),
                    esc_html( implode( ', ', (array) $j['deps'] ) ?: 'なし' ),
                    $j['in_footer'] ? 'yes' : 'no'
                );
            }
        }

        echo "</div>";
    }

    /**
     * エラーメッセージのレンダリング
     */
    public function render_error( $message ) {
        echo '<div style="border:2px solid red;padding:10px;background:#fff0f0;">';
        echo '⚠️ <strong>PageInfoViewer:</strong> ' . esc_html( $message );
        echo '</div>';
    }
}