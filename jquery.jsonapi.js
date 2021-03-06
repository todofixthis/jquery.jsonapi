/**
 * Copyright (c) 2011 J. Walter Thompson dba JWT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/** Defines $.fn.jsonapi() for adding triggers to elements to send form data to
 *    JsonApi actions.
 *
 * @param Object $options
 *
 *  Standard Options:
 *  - url:                    URL to send Ajax request to.  If not specified,
 *                             the action of the [parent] form will be used.
 *
 *                             For non-form-related elements, this option needs
 *                             to be set explicitly.
 *
 *                            This option can be a function that returns a URL.
 *                             The function will be executed after the
 *                             pre_execute() hook (see below).
 *
 *                            Parameters:
 *                             - jQuery $element Element that triggered the
 *                                jsonapi call.
 *
 *                            If the function returns (bool) false, the Ajax
 *                              request will not fire.  Note that post_execute()
 *                              will still be executed if url() returns false
 *                              (see below).
 *
 *  Hooks:
 *  - pre_execute:            Runs as soon as the plugin is triggered.
 *                             Return (bool) false to cancel the Ajax request.
 *                             Note that post_execute() will still be executed
 *                             if pre_execute() returns false (see below).
 *
 *                             Parameters:
 *                              - jQuery $element Element that triggered the
 *                                 jsonapi call.
 *                              - Event  $event   Event that triggered the
 *                                 jsonapi call.
 *
 *  - before_send:            Runs immediately before sending the ajax request.
 *                              This method has the same signature and purpose
 *                              as jQuery's `beforeSend` handler.
 *                            @see http://api.jquery.com/Ajax_Events/
 *
 *  - success:                Runs when Ajax response has status of 'ok'.
 *                             Return value is ignored.
 *
 *                             Parameters:
 *                              - Object $res     Result object from Ajax call.
 *                              - jQuery $element Element that triggered the
 *                                 jsonapi call.
 *                              - Object $data    Data that were sent to remote.
 *
 *  - failure:                Runs when Ajax response has status of 'fail'.
 *                             Return value is ignored.
 *
 *                             Parameters:
 *                              - Object $res     Result object from Ajax call.
 *                              - jQuery $element Element that triggered the
 *                                 jsonapi call.
 *                              - Object $data    Data that were sent to remote.
 *
 *  - error:                  Runs when Ajax response is not decipherable or has
 *                             an invalid status value.  Return value is
 *                             ignored.
 *
 *                             Parameters:
 *                              - Error          $err     Exception that was
 *                                 thrown.
 *                              - jQuery         $element Element that triggered
 *                                 the jsonapi call.
 *                              - Object         $data    Data that were sent to
 *                                 remote.
 *                              - XMLHttpRequest $xhr     XMLHttpRequest object
 *                                 created during the request.
 *
 *  - post_execute:           Runs upon completion of Ajax request regardless of
 *                             status.  Return value is ignored.
 *
 *                             Note that post_execute() ALWAYS runs, regardless
 *                              of whether the result of the Ajax call was
 *                              success(), failure() or error(), or if
 *                              pre_execute() returned false to prevent the Ajax
 *                              call from firing.
 *
 *                             Parameters:
 *                              - jQuery      $element Element that triggered
 *                                 the jsonapi call.
 *                              - Object|null $data    Data that were sent to
 *                                 remote, or null if called as a result of
 *                                 pre_execute() returning false (see above).
 *                              - String      $from    Name of the method that
 *                                 invoked post_execute():
 *                                  - "pre_execute"
 *                                  - "url"
 *                                  - "data"
 *                                  - "cached"
 *                                  - "success"
 *                                  - "failure"
 *                                  - "error"
 *
 *  Advanced Options:
 *  - async:                  If true (default), the Ajax request will be
 *                             asynchronous.
 *
 *                             Note that synchronous ajax (sjax?) on the main
 *                              thread is not allowed in modern browsers.
 *
 *  - cache_key:              Specifies the key to use when determining whether
 *                             to make an ajax request or use locally-cached
 *                             response data.
 *
 *                             You can pass a function here; it will be
 *                              evaluated immediately before sending the Ajax
 *                              request and should return a string or (bool)
 *                              false.
 *
 *                             Parameters:
 *                              - String $url   The request URL.
 *                              - Object $data  The request data.
 *
 *                             If the function returns (bool) false, the
 *                              response data will not be cached.
 *
 *                             Note that by default, responses are NOT cached.
 *                              If you want to use caching, you MUST specify a
 *                              function for this option.
 *
 *                             Note also that only success responses are cached;
 *                              failure responses are never cached.
 *
 *  - cached:                 Runs when using a cached response instead of
 *                             making an ajax call (see `cache_key` above).
 *
 *                            By default, this just invokes the success() hook.
 *
 *  - data:                   Data to send with the HTTP request, specified as
 *                             a key/value object, query string or function that
 *                             returns same.
 *
 *                             If not specified, the element's parent form (or
 *                              the element itself, if it is a form) will be
 *                              serialize()'d, and the result will be used as
 *                              the value of this option.
 *
 *                             You can pass a function here; it will be
 *                              evaluated immediately before sending the Ajax
 *                              request and should return an object or query
 *                              string.
 *
 *                             Parameters:
 *                              - jQuery $element Element that triggered the
 *                                 jsonapi call.
 *
 *                             If the function returns (bool) false, the Ajax
 *                              request will not fire.  Note that post_execute()
 *                              will still be executed even if $data() returns
 *                              false.
 *
 *                             Note that $data will not be evaluated if
 *                              pre_execute() returns false.
 *
 *  - jsonp:                  Whether to send the request using JSONP.  Default
 *                              is null (auto-detect).
 *
 *  - method:                 HTTP request method.  Default is 'post' (or auto-
 *                              detected if the element is a form).
 *
 *  - return:                 Return value after event handler fires.  Default
 *                             is false to prevent default event handler from
 *                             firing after jsonapi.
 *
 *                             Note that if you instruct jsonapi to return true,
 *                              the default event handler will likely fire
 *                              *before* the Ajax call returns.  Watch out for
 *                              race conditions!
 *
 *                             Consider whether you can implement the desired
 *                              functionality in post_execute before relying on
 *                              this option.
 *
 * - traditional:            Set this to true if you wish to use the traditional
 *                              style of param serialization.
 *
 *                              See http://api.jquery.com/jQuery.param/ for more
 *                                info.
 *
 * - trigger:                Specify the event to listen for.
 *                             Defaults:
 *                              - forms:            'submit'
 *                              - select boxes:     'change'
 *                              - textual inputs:   'change'
 *                              - everything else:  'click'
 *
 *                             You may specify a
 *                              space-delimited list of events if more than one
 *                              event can trigger the Ajax call.
 *
 * Note that $.fn.jsonapi() is a wrapper for $.jsonapi(), which you can use to
 *  make JsonApi calls outside the context of an HTML element.  The parameters
 *  and behavior of $.jsonapi() are identical to those of $.fn.jsonapi(), sans
 *  anything related to HTML elements.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage web
 */
(function( $ ) {
  /** Caches response data locally.  See `cache_key` documentation above. */
  var cache = {};

  /** $.jsonapi() for making an ad-hoc JsonApi request.
   *
   * Also leveraged by $.fn.jsonapi() below.
   */
  $.extend({'jsonapi': function( $options ) {
    //noinspection AssignmentToFunctionParameterJS
    $options = $.extend(
      {
        /* Standard Options */
        'url':                    '',

        /* Hooks */
        'pre_execute':            null,
        'before_send':            null,
        'success':                null,
        'failure':                null,
        'error':                  null,
        'post_execute':           null,

        /* Advanced Options */
        'async':                  true,
        'cache_key':              false,
        'cached':                 null,
        'data':                   null,
        'jsonp':                  null,
        'method':                 'post',
        'traditional':            false
      },
      ($options || {})
    );

    var $data;

    /** Executes the post-execute handler.
     *
     * @param $data Object
     * @param $from String
     *
     * @return void
     */
    function _postExecute( $data, $from ) {
      if( typeof($options.post_execute) == 'function' ) {
        $options.post_execute($data, $from);
      }
    }

    /** Handle an exception from the Ajax call.
     *
     * @param $err  Error
     * @param $xhr  XMLHttpRequest
     *
     * @return Boolean
     */
    function _handleException( $err, $xhr ) {
      if( typeof($options.error) == 'function' ) {
        if( typeof($err.constructor) == 'undefined' || $err.constructor != Error ) {
          //noinspection AssignmentToFunctionParameterJS
          $err = new Error($err);
        }

        /* $err is kind of like $res, so it goes before $data. */
        $options.error($err, $data, $xhr);
      }

      _postExecute($data, 'error');
    }

    try
    {
      /* Call pre_execute hook.  Use return value to determine whether to
       *  continue.
       */
      if( typeof($options.pre_execute) == 'function' ) {
        if( $options.pre_execute() === false ) {
          return _postExecute(null, 'pre_execute');
        }
      }

      while( typeof($options.url) == 'function' ) {
        $options.url = $options.url()
      }

      /* The only value in $options that *must* have a value is url. */
      if( $options.url === false ) {
        return _postExecute(null, 'url');
      } else {
        //noinspection EqualityComparisonWithCoercionJS
        if( $options.url == '' ) {
          return _handleException('url option not set.', null);
        }
      }

      $data = $options.data;
      while( typeof($data) == 'function' ) {
        $data = $data();
      }

      if( $data === false ) {
        return _postExecute(null, 'data');
      }
    }
    catch( $err ) {
      return _handleException($err, null);
    }

    if( ! ($options.jsonp || ($options.jsonp === false)) )
    {
      /* Check to see if we should use jsonp (for cross-domain request). */
      var $target = $(document.createElement('a'))
        .prop('href', $options.url)
        .get(0)
        .hostname;

      $options.jsonp = ($target !== location.hostname);
    }

    /* Check to see if we are using the cache. */
    var cache_key = null;
    if( typeof($options.cache_key) == 'function' ) {
        cache_key = $options.cache_key($options.url, $data);
    }

    /* Check for cache hit. */
    if( cache_key && cache.hasOwnProperty(cache_key) ) {
      try {
        /* Prefer the 'cached' handler, but fall back on 'success'. */
        if( typeof($options.cached) == 'function' ) {
            $options.cached(cache[cache_key], $data);
        } else if( typeof($options.success) == 'function' ) {
            $options.success(cache[cache_key], $data);
        }

        /* Else raise exception?  This would be a pretty weird case.
         *
         *  Ehhhh, we'll assume the developer knows what he's doing.  That's
         *      usually a safe assumption.
         *
         *  Usually.
         */

        return _postExecute($data, 'cached');
      } catch( $err ) {
        return _handleException($err, null);
      }
    }

    //noinspection JSUnusedLocalSymbols
    $.ajax({
      'async':        $options.async,
      'type':         $options.method,
      'url':          $options.url,

      'data':         $data,
      'dataType':     ($options.jsonp ? 'jsonp' : 'json'),
      'traditional':  $options.traditional,
      'beforeSend':   $options.before_send,

      // https://bugs.jquery.com/ticket/12326
      'contentType':  'application/json',

      'success':  function( $res, $status, $xhr ) {
        try {
          if( typeof($res.status) != 'undefined' ) {
            if( $res.status == 'ok' ) {
              if( typeof($options.success) == 'function' ) {
                $options.success($res.detail, $data);
              }

              /* If we are caching, store the result in the local cache. */
              if( cache_key ) {
                  cache[cache_key] = $res.detail;
              }
            }
            /* Allow 200 failures if the request uses JSONP. */
            else if( $res.status == 'fail' ) {
              if( typeof($options.failure) == 'function' ) {
                $options.failure($res.detail, $data);
              }
            }

            _postExecute($data, 'success');
          } else {
            //noinspection ExceptionCaughtLocallyJS
            throw new Error('Malformed success response from server.');
          }
        } catch( $err ) {
          _handleException($err, $xhr);
        }
      },

      'error':    function( $xhr, $status, $exc ) {
        try {
          if($.inArray($xhr.status, [200, 400]) === -1) {
            //noinspection ExceptionCaughtLocallyJS
            throw new Error(
              'XmlHttpRequest received ' + $xhr.status + ' response from server (jQuery says: ' + $status + ').'
            );
          }

          var $res = $.parseJSON($xhr.responseText);

          if( ! $res ) {
            //noinspection ExceptionCaughtLocallyJS
            throw new Error('No response from server.');
          }

          if( typeof($res.status) != 'undefined' && $res.status == 'fail' ) {
            if( typeof($options.failure) == 'function' ) {
              $options.failure($res.detail, $data);
            }

            _postExecute($data, 'failure');
          } else {
            //noinspection ExceptionCaughtLocallyJS
            throw new Error('Malformed error response from server.');
          }
        } catch( $err ) {
          _handleException($err, $xhr);
        }
      }
    });
  }});

  /** $.fn.jsonapi() for adding JsonApi triggers to object events.
   *
   * See document docblock for more information.
   */
  $.extend($.fn, {'jsonapi': function( $options ) {
    //noinspection AssignmentToFunctionParameterJS
    $options = $.extend(
      {
        /* Standard Options */
        'url':                    '',

        /* Hooks */
        'pre_execute':            null,
        'before_send':            null,
        'success':                null,
        'failure':                null,
        'error':                  null,
        'post_execute':           null,

        /* Advanced Options */
        'async':                  true,
        'cache_key':              false,
        'cached':                 null,
        'data':                   null,
        'method':                 null,
        'return':                 false,
        'traditional':            false,
        'trigger':                ''
      },
      ($options || {})
    );

    /** Executes the post-execute handler.
     *
     * @param $data Object
     * @param $from String
     *
     * @return void
     */
    function _postExecute( $data, $from ) {
      if( typeof($options.post_execute) == 'function' ) {
        $options.post_execute($data, $from);
      }
    }

    /* Could be called on multiple elements, and the default behavior will be
     *  slightly different depending on each element.
     */
    $(this).each(function(  ) {
      var $this     = $(this);
      var $tagName  = String(this.tagName).toLowerCase();

      var $trigger;
      if( $options.trigger ) {
        //noinspection JSUnusedAssignment
        $trigger = $options.trigger;
      } else {
        switch( $tagName )
        {
          case 'form':
            $trigger = 'submit';
          break;

          case 'select':
          case 'textarea':
            $trigger = 'change';
          break;

          case 'input':
            switch( $this.prop('type') ) {
              case 'text':
              case 'password':
                $trigger = 'change';
              break;

              default:
                $trigger = 'click';
              break;
            }
          break;

          default:
            $trigger = 'click';
          break;
        }
      }

      $this.on($trigger, function( $event ) {
        /* Call pre_execute hook.  Use return value to determine whether to
         *  continue.
         *
         * We have to duplicate a little code here to avoid calling
         *  $options.data() if pre_execute() returns false.
         */
        if( typeof($options.pre_execute) == 'function' ) {
          if( $options.pre_execute($this, $event) === false ) {
            _postExecute(null, 'pre_execute');
            return $options.return;
          }
        }

        /* URL could be different for each element in the selector.  Create
         *  local variable to determine.
         */
        var $url = $options.url;
        while( typeof($url) == 'function' ) {
          $url = $url($this);
        }

        /* Last-ditch effort to determine a default value for $options.url. */
        if( (! $url) && ($url !== false) ) {
          if( $tagName == 'form' ) {
            $url = $this.prop('action');
          }
          else if( $tagName == 'a' ) {
            $url = $this.prop('href');
          } else {
            $url = $this.parents('form:first').prop('action');
          }
        }

        /* Double-check to see if we know how we are sending the data. */
        var $method = $options.method;
        if( (! $method) && ($tagName === 'form') )
        {
          $method = $this.prop('method');
        }

        /* Post by default. */
        if( ! $method )
        {
          $method = 'post';
        }

        /* Same goes for the request data. */
        var $data = $options.data;
        while( typeof($data) == 'function' ) {
          $data = $data($this);
        }

        if( ! $data ) {
          if( $data === false ) {
            return _postExecute(null, 'data');
          } else if( $tagName == 'form' ) {
            $data = $this.serialize();
          } else {
            $data = $this.parents('form:first').serialize();
          }
        }

        $.jsonapi($.extend(
          {},
          $options,
          {
            'url':          $url,
            'data':         $data,
            'method':       $method,
            'traditional':  $options.traditional,

            'pre_execute':  null, // If it's defined, we already called it!

            'success':      function( $res, $data ) {
              if( typeof($options.success) == 'function' ) {
                return $options.success($res, $this, $data);
              }
            },

            'failure':      function( $res, $data ) {
              if( typeof($options.failure) == 'function' ) {
                return $options.failure($res, $this, $data);
              }
            },

            'error':        function( $err, $data, $xhr ) {
              if( typeof($options.error) == 'function' ) {
                return $options.error($err, $this, $data, $xhr);
              }
            },

            'post_execute': function( $data, $from ) {
              if( typeof($options.post_execute) == 'function' ) {
                $options.post_execute($this, $data, $from);
              }
            },

            'cache_key':    $options.cache_key,
            'cached':       $options.cached
          }
        ));

        return $options['return'];
      });
    });

    return this;
  }});
})(jQuery);
