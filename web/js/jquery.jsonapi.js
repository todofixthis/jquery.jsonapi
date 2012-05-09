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
 *  Hooks:
 *  - pre_execute:            Runs immediately before sending the Ajax request.
 *                             Return false to cancel the Ajax request.  Note
 *                             that post_execute() will still be executed if
 *                             pre_execute() returns false (see below).
 *
 *                             Parameters:
 *                              - jQuery $element Element that triggered the
 *                                 jsonapi call.
 *                              - Event  $event   Event that triggered the
 *                                 jsonapi call.
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
 *                              success(), error() or exception(), or if
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
 *                                  - "success"
 *                                  - "failure"
 *                                  - "error"
 *
 *  Advanced Options:
 *  - async:                  If true (default), the Ajax request will be
 *                             asynchronous.
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
 *                             Note that $data will not be evaluated if
 *                              pre_execute() returns false.
 *
 *  - method:                 HTTP request method.  Default is 'post'.
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
        'success':                null,
        'failure':                null,
        'error':                  null,
        'post_execute':           null,

        /* Advanced Options */
        'async':                  true,
        'method':                 'post',
        'data':                   null
      },
      ($options || {})
    );

    var $data;

    /** Handle an exception from the Ajax call.
     *
     * @param $err  Error
     * @param $xhr  XMLHttpRequest
     *
     * @return void
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

      if( typeof($options.post_execute) == 'function' ) {
        $options.post_execute($data, 'error');
      }

      return false;
    }

    try
    {
      if( typeof($options.url) == 'function' ) {
        $options.url = $options.url()
      }

      /* Call pre_execute hook.  Use return value to determine whether to
       *  continue.
       */
      if( typeof($options.pre_execute) == 'function' ) {
        if( $options.pre_execute() === false ) {
          if( typeof($options.post_execute) == 'function' ) {
            $options.post_execute(null, 'pre_execute');
          }

          return false;
        }
      }

      /* The only value in $options that *must* have a value is url. */
      //noinspection EqualityComparisonWithCoercionJS
      if( $options.url == '' ) {
        return _handleException('url option not set.');
      }

      if( typeof($options.data) == 'function' ) {
        $data = $options.data();
      } else {
        $data = ($options.data || {});
      }
    }
    catch( $err ) {
      return _handleException($err);
    }

    //noinspection JSUnusedLocalSymbols
    $.ajax({
      async:    $options.async,
      type:     $options.method,
      url:      $options.url,
      data:     $data,
      dataType: 'json',

      success:  function( $res, $status, $xhr ) {
        try {
          if( typeof($res.status) != 'undefined' && $res.status == 'ok' ) {
            if( typeof($options.success) == 'function' ) {
              $options.success($res.detail, $data);
            }

            if( typeof($options.post_execute) == 'function' ) {
              $options.post_execute($data, 'success');
            }
          } else {
            //noinspection ExceptionCaughtLocallyJS
            throw new Error('Malformed success response from server.');
          }
        } catch( $err ) {
          _handleException($err, $xhr);
        }
      },

      error:    function( $xhr, $status ) {
        try {
          var $res = $.parseJSON($xhr.responseText);

          if( ! $res ) {
            //noinspection ExceptionCaughtLocallyJS
            throw new Error('No response from server.');
          }

          if( typeof($res.status) != 'undefined' && $res.status == 'fail' ) {
            if( typeof($options.failure) == 'function' ) {
              $options.failure($res.detail, $data);
            }

            if( typeof($options.post_execute) == 'function' ) {
              $options.post_execute($data, 'failure');
            }
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
        'success':                null,
        'failure':                null,
        'error':                  null,
        'post_execute':           null,

        /* Advanced Options */
        'trigger':                '',
        'async':                  true,
        'method':                 'post',
        'data':                   null,
        'return':                 false
      },
      ($options || {})
    );

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
            switch( $this.attr('type') ) {
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

      $this.bind($trigger, function( $event ) {
        var $this = $(this);

        /* URL could be different for each element in the selector.  Create
         *  local variable to determine.
         */
        var $url = $options.url;
        if( typeof($url) == 'function' ) {
          $url = $options.url($this);
        }

        /* Last-ditch effort to determine a default value for $options.url. */
        //noinspection EqualityComparisonWithCoercionJS
        if( $url == '' ) {
          if( $tagName == 'form' ) {
            $url = $this.attr('action');
          } else {
            $url = $this.parents('form:first').attr('action');
          }
        }

        /* Call pre_execute hook.  Use return value to determine whether to
         *  continue.
         *
         * We have to duplicate a little code here to avoid calling
         *  $options.data() if pre_execute() returns false.
         */
        if( typeof($options.pre_execute) == 'function' ) {
          if( $options.pre_execute($this, $event) === false ) {
            if( typeof($options.post_execute) == 'function' ) {
              $options.post_execute($this, null, 'pre_execute');
            }

            return false;
          }
        }

        $.jsonapi($.extend(
          {},
          $options,
          {
            'url':          $url,

            'data':         function(  ) {
              if( typeof($options.data) == 'function' ) {
                return $options.data($this);
              } else if( $options.data ) {
                return $options.data;
              } else if( $tagName == 'form' ) {
                return $this.serialize();
              } else {
                return $this.parents('form:first').serialize();
              }
            },

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
                return $options.post_execute($this, $data, $from);
              }
            }
          }
        ));

        return $options['return'];
      });
    });

    return this;
  }});
})(jQuery);