window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');

$(document).ready(function() {

});

var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix.sass('app.scss')
       .browserify('app.js');
});
