<?php

class jbauthoauth_widget extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'jbauthoauth_widget', 'description' => "Show JBAuth login button in a Widget");
        parent::__construct('jbauthoauth_widget', "JBAuth Login Widget", $widget_ops);
    }

    function widget($args, $instance) {
        extract($args);
        echo $before_widget;
        $title = $instance["jbauthoauth_title"];
        $description = $instance["jbauthoauth_descr"];
        if (isset($title))
            echo "<h3 class=\"widget-title\">" . $title . "</h3>";
        echo "<p>";
        echo do_shortcode('[jbauthbtn]');
        echo "</p>";
        if (isset($description))
            echo "<p>" . $description . "</p>";
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance["jbauthoauth_title"] = strip_tags($new_instance["jbauthoauth_title"]);
        $instance["jbauthoauth_descr"] = strip_tags($new_instance["jbauthoauth_descr"]);
        return $instance;
    }

    function form($instance) {
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('jbauthoauth_title'); ?>"><?php _e('Title', 'jbauth_oauth'); ?></label>
            <input type="text" id="<?php echo $this->get_field_id('jbauthoauth_title'); ?>" name="<?php echo $this->get_field_name('jbauthoauth_title'); ?>" <?php if (isset($instance["jbauthoauth_title"])) { ?> value="<?php echo $title = $instance["jbauthoauth_title"]; ?>" <?php } ?>>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('jbauthoauth_descr'); ?>"><?php _e('Description', 'jbauth_oauth'); ?></label>
            <input type="text" id="<?php echo $this->get_field_id('jbauthoauth_descr'); ?>" name="<?php echo $this->get_field_name('jbauthoauth_descr'); ?>" <?php if (isset($instance["jbauthoauth_descr"])) { ?> value="<?php echo $title = $instance["jbauthoauth_descr"]; ?>" <?php } ?>>
        </p>
        <?php
    }

}
?>