<?php

return json_decode(json_encode([
  "theme" => "monotai.com",
  "base_url" => "",
  "tag_path" => "tags",
  "title" => "A Demo Page",
  "tagline" => "yes, just a demo",
  "description" => "Lorem ipsum dolor sit amet.",
  "language" => "en-US",
  "author" => "Alexander Thomas",
  "slugs" => [
    "post_index" => "blog",
    "feed" => "feed",
  ],
  "menus" => [
    "main" => [
      ["internal" => "index", "label" => "Home"],
      ["internal" => "blog", "label" => "Blog"],
      ["internal" => "about", "label" => "About", "type" => "content"],
    ],
    "social" => [
      ["external" => "https://twitter.com/fanatique", "label" => "Twitter"],
      ["external" => "https://github.com/fanatique", "label" => "Github"],
      ["internal" => "feed", "label" => "Feed", "type" => "feed"],
    ],
    "footer" => [
      ["internal" => "imprint", "label" => "Imprint", "type" => "content"],
      ["internal" => "privacy-policy", "label" => "Privacy Policy", "type" => "content"],
    ]
  ],
  "twitter_handle" => "@fanatique",
  "github_handle" => "@fanatique",
  "folders" => [
    "assets" => "assets",
    "dist" => __DIR__ . "/../../../dist",
    "dist_tags" => __DIR__ . "/../../../dist/tags",
    "content_images" => __DIR__ . "/../../page/content/img",
    "dist_content_images" => __DIR__ . "/../../dist/img",
    "views" => __DIR__ . "/../views/",
    "content" => __DIR__ . "/../content/",
  ],
  "views" => [
    "post" => "post.phtml",
    "post_index" => "index.phtml",
    "page" => "page.phtml",
    "tag" => "tag.phtml",
    "menus" => [
      "main" => "menus/main.phtml",
      "footer" => "menus/footer.phtml",
      "social" => "menus/social.phtml"
    ]
  ],
  "content" => [
    "posts" => "posts",
    "pages" => "pages"
  ],
  "file_extension_content" => "html",
  "file_extension_feed" => "xml",
]));
