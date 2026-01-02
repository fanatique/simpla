<?php

return json_decode(json_encode([
  "theme" => "default", // Defining which theme to use. It refers to the collection of templates, that should be found under `page/views/[THEME]/...`
  "base_url" => "", // The full URL under which your site can be found.
  "title" => "A Demo Page", // That should be the main title of your site. It's used in the `header` as well as in the meta tags.
  "tagline" => "yes, just a demo", // A succinct description of your website.
  "description" => "Lorem ipsum dolor sit amet.", //  A short description of this website, which is being used for the description meta tag.
  "language" => "en-US", // A language code, that sets the document's language attribute (i.e. `en-US` for American English). For more details, see: https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang
  "author" => "Alexander Thomas", // A default author that is used if a page or a post does not specify one.
  "slugs" => [
    "post_index" => "blog", // defines the filename of the index of the latest blog posts should be stored.
    "feed" => "feed",
  ],
  "tag_path" => "tags", //URL namespace under which tag related index pages are being published
  "menus" => [
    "main" => [
      ["internal" => "index", "label" => "Home"],
      ["internal" => "blog", "label" => "Blog"],
      ["internal" => "about", "label" => "About", "type" => "content"],
    ],
    "social" => [
      ["external" => "https://github.com/fanatique", "label" => "Github"],
      ["internal" => "feed", "label" => "Feed", "type" => "feed"],
    ],
    "footer" => [
      ["internal" => "imprint", "label" => "Imprint", "type" => "content"],
      ["internal" => "privacy-policy", "label" => "Privacy Policy", "type" => "content"],
    ]
  ],
  "folders" => [
    "assets" => "assets",
    "dist" => __DIR__ . "/../../dist",
    "dist_tags" => __DIR__ . "/../../dist/tags",
    "content_images" => __DIR__ . "/../../page/content/img",
    "dist_content_images" => __DIR__ . "/../../dist/img",
    "views" => __DIR__ . "/../views/",
    "content" => __DIR__ . "/../content/",
  ],
  "images" => [
    "max_width" => 1920, // Set to null to disable width downscaling
    "max_height" => 1080, // Set to null to disable height downscaling
    "generate_webp" => true, // Also emit WebP versions next to originals
    "webp_quality" => 82, // 0-100
    "jpeg_quality" => 82, // 0-100
    "png_compression" => 6, // 0 (no compression) to 9 (max)
  ],
  "views" => [
    "post" => "post.phtml",
    "post_index" => "index.phtml",
    "page" => "page.phtml",
    "tag" => "tag.phtml",
    "menus" => [
      "main" => "menus/main.phtml",
      "footer" => "menus/footer.phtml",
      "social" => "menus/social.phtml",
    ]
  ],
  "content" => [
    "posts" => "posts",
    "pages" => "pages",
    "snippets" => "snippets",
  ],
  "file_extension_content" => "html",
  "file_extension_feed" => "xml",
]));
