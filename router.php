<?php

// router.php

// Get the url path and trim leading slash
$url_path = $_SERVER[ 'REQUEST_URI' ];
// If url_path is empty, it is root, so call index.html
if ( ! $url_path ||  ! preg_match( '/[.]/', $url_path ) ) {
    include( 'index.php' );
    return;
}

// If url_path has no dot, it is a post permalink, so add .html extension
//if( ! preg_match( '/[.]/', $url_path ) ) {
//    include( $url_path . '.' );
//    return;
//}

// In case of css files, add the appropriate header
if( preg_match( '/\.css$/', $url_path ) ) {
    header("Content-type: text/css");
    include( __DIR__ . '/page/views/assets/' . $url_path );
}

if( preg_match( '/\.js$/', $url_path ) ) {
    header("Content-type: application/javascript");
    include( __DIR__ . '/page/views/assets/' . $url_path );
}

if( preg_match( '/^\/img\//', $url_path ) ) {
    header("Content-type: image/jpeg");
    echo file_get_contents( __DIR__ . '/page/content/' . $url_path );
}
