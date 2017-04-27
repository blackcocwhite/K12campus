const elixir = require('laravel-elixir');
/**
 * Default gulp is to run this elixir stuff
 */
elixir(function(mix) {
    mix.browserSync({
        proxy:"http://k12.edu:8080"
    })
});
