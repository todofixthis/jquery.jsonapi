# 1.1.0
## Major Changes
- Added JSONP support (tested with jQuery).
- Added `JsonApi_Actions->bindForm()` and handling of form validation errors.

## Minor Changes
### PHP
- Added `$default` argument to `JsonApi_Actions->getParam()`.
- `JsonApi_Actions->requireMethod()` now accepts multiple arguments.
- Added `JsonApi_Actions->requirePut()`.
- Made `required` failure message for `JsonApi_Actions->bindForm()`
  customizable.
- Made `JsonApi_Actions->validate()` part of the API.

### Javascript
- Try to infer default value for `url` and `method` options when
  `$.fn.jsonapi()` is invoked on a form or input.
- `url` option can now be a function.
- Return false from `url` or `data` function to cancel ajax request.
- Added event object as argument to `$.fn.jsonapi:pre_execute()`.

## Miscellaneous
- Lots of refactoring and minor optimizations.

# 1.0.0
- Initial release.