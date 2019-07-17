# Litteratursiden Theme
Theme based on "Sass Starterkit", see separate readme for instructions.   

## Dependencies
Currently requires Node 6 to build because of legacy dependencies. 

## Build
 A `node:6` image is part of the projects docker file. To run the `gulp` build
 steps do:
 
 ```
 docker-compose run --rm node bash -c "cd /app/web/themes/custom/litteratursiden/ && npm install"
 docker-compose run --rm node bash -c "cd /app/web/themes/custom/litteratursiden/ && node_modules/.bin/gulp sass" 
 ``` 
