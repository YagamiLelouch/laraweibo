(function () {

    var laroute = (function () {

        var routes = {

            absolute: false,
            rootUrl: 'http://localhost',
            routes : [{"host":null,"methods":["GET","HEAD"],"uri":"\/","name":"home","action":"App\Http\Controllers\MainpageController@index"},{"host":null,"methods":["POST"],"uri":"publish","name":"publish","action":"App\Http\Controllers\MainpageController@publish"},{"host":null,"methods":["POST"],"uri":"upload","name":"upload","action":"App\Http\Controllers\MainpageController@upload"},{"host":null,"methods":["POST"],"uri":"pComment","name":"pComment","action":"App\Http\Controllers\CommentController@pComment"},{"host":null,"methods":["POST"],"uri":"getComment","name":"getComment","action":"App\Http\Controllers\CommentController@getComment"},{"host":null,"methods":["GET","HEAD"],"uri":"people\/{username}","name":"people","action":"App\Http\Controllers\PeopleController@index"},{"host":null,"methods":["GET","HEAD"],"uri":"search","name":"search","action":"App\Http\Controllers\SearchController@index"},{"host":null,"methods":["GET","HEAD"],"uri":"islogin","name":"islogin","action":"App\Http\Controllers\SignController@isLogin"},{"host":null,"methods":["PUT"],"uri":"addcollection","name":"addcolllection","action":"App\Http\Controllers\CollectionController@addCollection"},{"host":null,"methods":["DELETE"],"uri":"deletecollection","name":"deletecollection","action":"App\Http\Controllers\CollectionController@deleteCollection"},{"host":null,"methods":["GET","HEAD"],"uri":"profile","name":"profile","action":"App\Http\Controllers\UserController@profile"},{"host":null,"methods":["POST"],"uri":"edit","name":"edit","action":"App\Http\Controllers\UserController@edit"},{"host":null,"methods":["GET","HEAD"],"uri":"password","name":"password","action":"App\Http\Controllers\UserController@password"},{"host":null,"methods":["POST"],"uri":"change_password","name":"change_password","action":"App\Http\Controllers\UserController@changePassword"},{"host":null,"methods":["GET","HEAD"],"uri":"collection","name":"collection","action":"App\Http\Controllers\UserController@collection"},{"host":null,"methods":["GET","HEAD"],"uri":"login","name":"login","action":"App\Http\Controllers\SignController@loginView"},{"host":null,"methods":["POST"],"uri":"login","name":"login","action":"App\Http\Controllers\SignController@login"},{"host":null,"methods":["GET","HEAD"],"uri":"logout","name":"logout","action":"App\Http\Controllers\SignController@logout"},{"host":null,"methods":["GET","HEAD"],"uri":"signup","name":"signup","action":"App\Http\Controllers\SignController@signUpView"},{"host":null,"methods":["POST"],"uri":"signup","name":"signup","action":"App\Http\Controllers\SignController@signUp"},{"host":null,"methods":["GET","HEAD"],"uri":"email_signup","name":"email_signup","action":"App\Http\Controllers\SignController@emailUp"},{"host":null,"methods":["POST"],"uri":"sendCode","name":"sendCode","action":"App\Http\Controllers\SignController@sendCode"},{"host":null,"methods":["GET","HEAD"],"uri":"forget","name":"forget","action":"App\Http\Controllers\SignController@forget"},{"host":null,"methods":["POST"],"uri":"reset\/email","name":"reset.email","action":"App\Http\Controllers\SignController@sendResetEmail"},{"host":null,"methods":["POST"],"uri":"reset\/phone","name":"reset.phone","action":"App\Http\Controllers\SignController@phoneReset"},{"host":null,"methods":["GET","HEAD"],"uri":"password\/reset\/{code}","name":"password.reset","action":"App\Http\Controllers\SignController@resetView"},{"host":null,"methods":["POST"],"uri":"password\/reset","name":"password.update","action":"App\Http\Controllers\SignController@reset"},{"host":null,"methods":["GET","HEAD"],"uri":"admin","name":"admin","action":"App\Http\Controllers\AdminController@index"},{"host":null,"methods":["POST"],"uri":"info","name":"info","action":"App\Http\Controllers\AdminController@info"},{"host":null,"methods":["POST"],"uri":"userAll","name":"userAll","action":"App\Http\Controllers\AdminController@userAll"},{"host":null,"methods":["GET","HEAD"],"uri":"_debugbar\/open","name":"debugbar.openhandler","action":"Barryvdh\Debugbar\Controllers\OpenHandlerController@handle"},{"host":null,"methods":["GET","HEAD"],"uri":"_debugbar\/clockwork\/{id}","name":"debugbar.clockwork","action":"Barryvdh\Debugbar\Controllers\OpenHandlerController@clockwork"},{"host":null,"methods":["GET","HEAD"],"uri":"_debugbar\/assets\/stylesheets","name":"debugbar.assets.css","action":"Barryvdh\Debugbar\Controllers\AssetController@css"},{"host":null,"methods":["GET","HEAD"],"uri":"_debugbar\/assets\/javascript","name":"debugbar.assets.js","action":"Barryvdh\Debugbar\Controllers\AssetController@js"}],
            prefix: '',

            route : function (name, parameters, route) {
                route = route || this.getByName(name);

                if ( ! route ) {
                    return undefined;
                }

                return this.toRoute(route, parameters);
            },

            url: function (url, parameters) {
                parameters = parameters || [];

                var uri = url + '/' + parameters.join('/');

                return this.getCorrectUrl(uri);
            },

            toRoute : function (route, parameters) {
                var uri = this.replaceNamedParameters(route.uri, parameters);
                var qs  = this.getRouteQueryString(parameters);

                if (this.absolute && this.isOtherHost(route)){
                    return "//" + route.host + "/" + uri + qs;
                }

                return this.getCorrectUrl(uri + qs);
            },

            isOtherHost: function (route){
                return route.host && route.host != window.location.hostname;
            },

            replaceNamedParameters : function (uri, parameters) {
                uri = uri.replace(/\{(.*?)\??\}/g, function(match, key) {
                    if (parameters.hasOwnProperty(key)) {
                        var value = parameters[key];
                        delete parameters[key];
                        return value;
                    } else {
                        return match;
                    }
                });

                // Strip out any optional parameters that were not given
                uri = uri.replace(/\/\{.*?\?\}/g, '');

                return uri;
            },

            getRouteQueryString : function (parameters) {
                var qs = [];
                for (var key in parameters) {
                    if (parameters.hasOwnProperty(key)) {
                        qs.push(key + '=' + parameters[key]);
                    }
                }

                if (qs.length < 1) {
                    return '';
                }

                return '?' + qs.join('&');
            },

            getByName : function (name) {
                for (var key in this.routes) {
                    if (this.routes.hasOwnProperty(key) && this.routes[key].name === name) {
                        return this.routes[key];
                    }
                }
            },

            getByAction : function(action) {
                for (var key in this.routes) {
                    if (this.routes.hasOwnProperty(key) && this.routes[key].action === action) {
                        return this.routes[key];
                    }
                }
            },

            getCorrectUrl: function (uri) {
                var url = this.prefix + '/' + uri.replace(/^\/?/, '');

                if ( ! this.absolute) {
                    return url;
                }

                return this.rootUrl.replace('/\/?$/', '') + url;
            }
        };

        var getLinkAttributes = function(attributes) {
            if ( ! attributes) {
                return '';
            }

            var attrs = [];
            for (var key in attributes) {
                if (attributes.hasOwnProperty(key)) {
                    attrs.push(key + '="' + attributes[key] + '"');
                }
            }

            return attrs.join(' ');
        };

        var getHtmlLink = function (url, title, attributes) {
            title      = title || url;
            attributes = getLinkAttributes(attributes);

            return '<a href="' + url + '" ' + attributes + '>' + title + '</a>';
        };

        return {
            // Generate a url for a given controller action.
            // laroute.action('HomeController@getIndex', [params = {}])
            action : function (name, parameters) {
                parameters = parameters || {};

                return routes.route(name, parameters, routes.getByAction(name));
            },

            // Generate a url for a given named route.
            // laroute.route('routeName', [params = {}])
            route : function (route, parameters) {
                parameters = parameters || {};

                return routes.route(route, parameters);
            },

            // Generate a fully qualified URL to the given path.
            // laroute.route('url', [params = {}])
            url : function (route, parameters) {
                parameters = parameters || {};

                return routes.url(route, parameters);
            },

            // Generate a html link to the given url.
            // laroute.link_to('foo/bar', [title = url], [attributes = {}])
            link_to : function (url, title, attributes) {
                url = this.url(url);

                return getHtmlLink(url, title, attributes);
            },

            // Generate a html link to the given route.
            // laroute.link_to_route('route.name', [title=url], [parameters = {}], [attributes = {}])
            link_to_route : function (route, title, parameters, attributes) {
                var url = this.route(route, parameters);

                return getHtmlLink(url, title, attributes);
            },

            // Generate a html link to the given controller action.
            // laroute.link_to_action('HomeController@getIndex', [title=url], [parameters = {}], [attributes = {}])
            link_to_action : function(action, title, parameters, attributes) {
                var url = this.action(action, parameters);

                return getHtmlLink(url, title, attributes);
            }

        };

    }).call(this);

    /**
     * Expose the class either via AMD, CommonJS or the global object
     */
    if (typeof define === 'function' && define.amd) {
        define(function () {
            return laroute;
        });
    }
    else if (typeof module === 'object' && module.exports){
        module.exports = laroute;
    }
    else {
        window.laroute = laroute;
    }

}).call(this);

