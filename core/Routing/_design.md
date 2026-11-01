## Initial design conception

1. plain php file like what laravel has with web.php
2. basic Middleware support
   1. Middleware contract
   2. side-effect-based terminate, or return $next
3. Registered in Application

---

## Buildout of MVC basics
- [x] Start by capturing any and all requests made to the app and dumping them 
- [x] Get basic GET working with redirecting rules, aka everything goes through my own router 
- [x] Setup controllers
- [ ] Create a functional Request and Response object wrapping the default php superglobals / other stuff
- [ ] Make routes actually resolve beyond the fallback
- [ ] Create a working minimal implementation of route wildcards handling (with regex BY HAND, scary shit)
  - [ ] required `{param}`
  - [ ] nullable `{?param}`
  - [ ] multiple parameters `{param1}/{?param2}` including the last one being nullable
  - [ ] spread parameters `{...params}`
- [ ] Figure out how Views will work in the app. 
  - [ ] Probably similar to blade's `Component`, where it's a class on the backend and html / php template on the FE.
  - [ ] Pass properties via `return new View('about', props: [...])` or something similar
  - [ ] (?) Livewire Volt style single-page components might be really cool but idk
  - [ ] (?) Also would like to try to make my own simple templating language with .html as the base but idk.

## Pseudocode conception
```php
class Router {}
class Middleware {}
class Route {}

// Option 1: builder pattern
Router::make()
    ->add(Route::post(...)
        ->
    )
    ->add(Route::get(...))

// web.php plain file
Route::get('/user/{id}', ControllerClass::)
    ->middleware([RequiresSignIn::class])
```