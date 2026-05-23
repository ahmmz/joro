<article class="content-article col-md-6">                   
    <div <?php post_class('news-item archive-item'); ?>>
        <?php
        do_action('envo_royal_archive_before_posts');
        $contents = get_theme_mod('blog_layout', array('image', 'title', 'excerpt', 'meta'));

        // Loop parts.
        foreach ($contents as $content) {
            do_action('envo_royal_archive_' . $content);
        }
        do_action('envo_royal_archive_after_posts');
        ?>
    </div>
</article>
