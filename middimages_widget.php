<?php
/*
  Plugin Name: Middlebury Photo of the Week
  Plugin URI: http://blogs.middlebury.edu/images
  Description: A widget that displays the latest images from the Middlebury Photo of the Week site.
  Version: 1.0
  Author: Ian McBride
  Author URI: http://go.middlebury.edu/ian
*/

const MIDDIMAGES_WIDGET_URL = "http://blogs.middlebury.edu/images";

class MiddImages_Widget extends WP_Widget {

  /**
   * Register Widget with WordPress
   */
  public function __construct() {
    parent::__construct(
      'middimages_widget',
      'MiddImages Widget',
      array( 'description' => __( 'Middlebury Photo of the Week', 'text_domain' ), )
    );
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form ( $instance ) {
    if ( !empty( $instance['title'] ) ) {
      $title = $instance['title'];
    }
    else {
      $title = __( 'Middlebury Photo of the Week', 'text_domain' );
    }
    if ( !empty( $instance['category'] ) ) {
      $category = $instance['category'];
    } else {
      $category = __( 'Photo of the Week', 'text_domain' );
    }
    if ( !empty( $instance['tag'] ) )
      $tag = $instance['tag'];
    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <p>Enter a category here and we'll include images from that category, or leave this blank to include all iamges on the site.</p>
    <p>
      <label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" type="text" value="<?php echo esc_attr( $category ); ?>" />
    </p>
    <p>OR enter a tag from the Middlebury Photo of the Week site and we'll include photos with that tag.</p>
    <p>
      <label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e( 'Tag:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>" type="text" value="<?php echo esc_attr( $tag ); ?>" />
    </p>
    <p>See <a href="<?php echo MIDDIMAGES_WIDGET_URL; ?>/categoriestags" target="_blank">a list of the Categories and Tags</a> on the Middlebury Photo of the Week site.
    <?php
  }

  /**
   * Sanitize the widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   */
  public function update ( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['category'] = strip_tags( $new_instance['category'] );
    $instance['tag'] = strip_tags( $new_instance['tag'] );

    return $instance;
  }

  /**
   * Front-end display of Widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget ( $args, $instance ) {
    extract( $args );
    wp_register_style( 'middimages-widget-style', plugins_url('middimages_widget.css', __FILE__) );
    wp_enqueue_style( 'middimages-widget-style' );

    $title = apply_filters( 'widget_title', $instance['title'] );
    $category = esc_attr( $instance['category'] );
    $tag = esc_attr( $instance['tag'] );
    $url = MIDDIMAGES_WIDGET_URL;

    echo $before_widget;
    if ( ! empty( $title) ) {
      echo $before_title . $title . $after_title;
    }

    if ( !empty($category) ) {
      $url .= '/category/' . str_replace(' ', '-', strtolower($category));
    } else if ( !empty($tag) ) {
      $url .= '/tag/' . $tag;
    }
    $url .= "/feed";

    $feed = fetch_feed( $url );

    if ( !is_wp_error( $feed) ) {
      $maxitems = $feed->get_item_quantity(5);

      $rss_items = $feed->get_items(0, $maxitems);
      for($i = 0; $i < count($rss_items); $i++) {
        $item = $rss_items[$i];
        $enclosure = $item->get_enclosure();

        echo '<a href="' . esc_url( $item->get_permalink() ) . '" title="' . esc_html( $item->get_title() ) . '">';

          echo '<img ' . ($i == 0 ? 'class="first" ' : '') . 'src="' . esc_url( $enclosure->get_link() ) . '">';

        echo '</a>';
      }
    }

    echo $after_widget;
  }

}

function middimages_widget_register_widgets() {
  register_widget( 'MiddImages_Widget' );  
}

add_action( 'widgets_init', 'middimages_widget_register_widgets' );