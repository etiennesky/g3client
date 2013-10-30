<?php
/*
   Copyright (C) 2011 by Florian Stoffel

   Permission is hereby granted, free of charge, to any person obtaining a copy
   of this software and associated documentation files (the "Software"), to deal
   in the Software without restriction, including without limitation the rights
   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   copies of the Software, and to permit persons to whom the Software is
   furnished to do so, subject to the following conditions:

   The above copyright notice and this permission notice shall be included in
   all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   THE SOFTWARE.
*/
include_once(dirname(__FILE__) . '/../client.php');
include_once(dirname(__FILE__) . '/../output/g3client_Output.php');

class G3Client_RandomPhotoWidget extends WP_Widget {

    function G3Client_RandomPhotoWidget(){
        $this->WP_Widget('g3client_randphotowidget', __('G3Client Random Photo', 'g3client'),
            array(
                'classname' => 'g3client_widget_randomphoto',
                'description' => __('Displays a random photo from a Gallery3', 'g3client')
            ));
    }

    function widget($args, $instance) {
        extract($args);

        $client = new G3Client(get_option(G3_SETTINGS_APIURL), get_option(G3_SETTINGS_APIKEY));
        $toShow = $client->getRandomPhoto();

        echo $before_widget;
        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
        if(!empty($title)) echo $before_title . $title . $after_title;

        if(isset($toShow['failure']) && $toShow['failure'] == false) {
            $result .= '<div class="g3client-widget-error error">' . sprintf(__('Could not retrieve random image (error code: %d)'), $toShow['http_status']) . '</div>';
        } else {
            $useLightbox = $instance['uselightbox'] ? true : false;
            $slug = !empty($toShow['slug']) ? $toShow['slug'] : $toShow['title'];

            echo '<div class="g3client_widget_randomphoto_container">';
            echo '<a href="' . $toShow['imgurl'] . '" title="' . $toShow['title'] . '"';
            echo 'class="' . G3Client_Output::getHrefCSS(array($useLightbox ? 'g3client_widget_random_photo_lightbox' : 'g3client_widget_random_photo')) . '">';
            echo '<img src="' . $toShow['thumb'] . '" alt="' . $slug . '">';
            echo '</a>';
            echo '</div>';
        }

        echo $after_widget;
    }

    function update($newInstance, $oldInstance) {
        $instance = $oldInstance;
        $instance['title'] = strip_tags($newInstance['title']);
        $instance['uselightbox'] = !empty($newInstance['uselightbox']) ? 1 : 0;

        return $instance;
    }

    function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => __('Random Photo', 'g3client')));
        $title = strip_tags($instance['title']);
        $uselightbox = isset($instance['uselightbox']) ? (bool) $instance['uselightbox'] : false;

        ?>
        <p>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </p>
            <p>
		        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('uselightbox'); ?>" name="<?php echo $this->get_field_name('uselightbox'); ?>"<?php checked($uselightbox); ?> />
		        <label for="<?php echo $this->get_field_id('uselightbox'); ?>"><?php _e('enable lightbox'); ?></label>
            </p>
        </p>
        <?php
    }

}

?>
