<?php
/*
Plugin Name: WP Recent Comments With Avatars
Plugin URI: http://sitehint.ru/?p=827
Description: Lightweight plugin that overrides the default widget "recent comments" in WordPress, adding an avatar display visitors comments and quotes length of 50 characters.
Version: 1.0
Author: TrueFalse
Author URI: http://red-book-cms.ru
License: GPLv2 or later
*/

require_once(ABSPATH . WPINC. '/default-widgets.php');

function WPRCWA_recent_comments() {
    unregister_widget("WP_Widget_Recent_Comments");
    register_widget("WPRCWA_WP_Widget_Recent_Comments");
}
add_action("widgets_init", "WPRCWA_recent_comments");

class WPRCWA_WP_Widget_Recent_Comments extends WP_Widget_Recent_Comments {

    function recent_comments_style() {
        if ( ! current_theme_supports( 'widgets' ) // Temp hack #14876
            || ! apply_filters( 'show_recent_comments_widget_style', true, $this->id_base ) )
            return;
        ?>
    <style type="text/css">
    ul#recentcomments {
      list-style: none;
      padding: 0;
      margin: 0;
    }    
    ul#recentcomments li.recentcomments {
      border-bottom: 1px solid #C6C6C6;
      margin: 0 0 8px;
      padding: 0 0 9px;
      min-height: 40px;
      background-image: none;
      list-style: none;
    }
    ul#recentcomments .alignleft {
      margin: 0 8px 0 0;
      padding: 0;
    }
    ul#recentcomments img.avatar {
      background-color: #FFFFFF;
      border: 1px solid #C6C6C6;
      box-shadow: none;
      padding: 4px;
      margin: 0;
    }
    </style>
<?php
    }

    function widget( $args, $instance ) {
        global $comments, $comment;

        $cache = wp_cache_get('widget_recent_comments', 'widget');

        if ( ! is_array( $cache ) )
            $cache = array();

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return;
        }

         extract($args, EXTR_SKIP);
         $output = '';
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Comments' ) : $instance['title'], $instance, $this->id_base );

        if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
             $number = 5;

        $comments = get_comments( apply_filters( 'widget_comments_args', array( 'number' => $number, 'status' => 'approve', 'post_status' => 'publish', 'type' => 'comment' ) ) );
        $output .= $before_widget;
        if ( $title )
            $output .= $before_title . $title . $after_title;

        $output .= '<ul id="recentcomments">';
        if ( $comments ) {
            // Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
            $post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
            _prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

            foreach ( (array) $comments as $comment) {
                $output .=  '<li class="recentcomments"><div class="alignleft">'. get_avatar($comment->comment_author_email, 32). '</div>'.
                /* translators: comments widget: 1: comment author, 2: post link */ sprintf(_x('%1$s on %2$s', 'widgets'), '<b>'. get_comment_author_link(). '</b>:',
                trim(mb_substr(strip_tags($comment->comment_content), 0, 50)). ' <a href="' . esc_url( get_comment_link($comment->comment_ID) ). '">&raquo;</a>') . '</li>';
            }
         }
        $output .= '</ul>';
        $output .= $after_widget;

        echo $output;
        $cache[$args['widget_id']] = $output;
        wp_cache_set('widget_recent_comments', $cache, 'widget');
    }

}
?>