# hanako-wp
A basic wordpress template based on Timber/Twig, TailwindCSS 4 ant Hanako TS.

## how to use
To run Vite compilation:

```npm run dev```

```npm run build```

## Todo
- Style Gravity Form Calender (trigger icon + calendar popup)

## Version

Last release 14.08.2026 (Beta)

### 2.0.0
Full refactoring of the theme to use TailwindCSS 4 instead of Bootstrap 5.3
– Replace Webpack with Vite
– Remove all Bootstrap 5.3 dependencies
- Refactor of the theme structure to use TailwindCSS 4
- Refactor PHP code structure
– Hanako TS updated to 2.0.2

### 1.6.12
- force jpg/png deletion after WebP conversion
- lazyloader: don't load 2x image on mobile devices
- remove other useless wp styles
- enable WebP by default for lazyloader
- fix an issue with console.log() in production mode
- update npm packages
- update composer packages

### 1.6.11
- add npm run dev command for development with auto reload
- update npm packages
- update composer packages

### 1.6.10
- fix source-map and license extraction in webpack config

### 1.6.9
- update composer and npm packages
- remove Wordpress useless images sizes
- remove useless Wordpress stuff in head
- add @2x support for lazyloader

### 1.6.8
- webpack configuration enhancements
- decline button in cookies consent

### 1.6.7
- complete refactoring of webpack configuration

### 1.6.6
- update composer and npm packages
- fix <title> tag issue
- fix CookiesConsent template and script

### 1.6.5
- new webpack configuration (faster)
- fix issue with wp-json and iTheme Security
- disable WP-Rocket optimizations
- various small fixes
- update npm packages
- update composer packages

### 1.6.4
- enhance webpack configuration
- extract license info from CSS files
- update CSS details
- update WPML language selector shortcode
- allow iTheme Security to access wp-json
- allow non logged users to make ajax requests and access wp-json
- update npm packages
- update composer packages

### 1.6.3
- disable wp-json for non logged users

### 1.6.2
- assets versioning to avoid cache issues
- bootstrap 5.3.3
- update npm packages
- remove Timmy
- remove ACF Options

### 1.6.1

- remove auto reload
- remove menu configuration
- remove post types and taxomies (please use ACF)
- clean meta tags
- correct use of wp localize script
- remove jquery dequeue
- allow Stipe API even if site in disabled for visitors

### 1.6.0

- Timber 2.0
– Updated npm packages

### 1.5.6

- CookiesConsent support «Do not track»

### 1.5.5

- Add ACF JSON file for CookiesConsent
- Separate npm dependancies

### 1.5.4

- RGPD & LPD cookies wall
- WP_AUTO_UPDATE_CORE -> minor
- Fix "taxonomies/taxonomy" misspelling

### 1.5.3

- Update npm packages
- Bootstrap 5.3
- Add support for dark mode

### 1.5.2

- Hanako-ts 1.2.7
- Bootstrap 5.2.3
- Remove debug.scss
- Rename RGDPConsent in CookiesConsent
- CookiesConsent disabled by default
- Add some frequently used modules (ScrollSpy, Carousel, Accordion, Collapse)
- Change Boostrap default options

### 1.5.1

- Hanako-ts 1.2.5
- No thumbnail for PDF files
- Disable Site Health widget
- Remove default theme stylesheet
- Editors can access Gravity Forms
- Editors can edit Privacy Policy Page
- Some admin menu cleaning
- Add Timmy to composer
- New setting to hide admin menus items
- New setting to change menu order

### 1.5

- Timber/Twig muss be installed via composer
- Hanako-ts 1.2.4
- remove "defer" on wp-login.php
- remove console and demo
- refactor twig files structure
- remove debug component
- bugfix lazyloader
- webp for lazyloader
- various functions added to functions/core

### 1.4.27

- Bootstrap 5.2

### 1.4.26

- Auto reload and dev mode are now independent
– PHP Errors reporting can be configured in backend

### 1.4.25

- Fix an issue with lazy loading image ratio

### 1.4.19

- Fix issue with defered scripts in admin

### 1.4.18

- Hanako-ts 1.2.3
- Bootstrap 5.2 beta
- defered js loading
- enhanced console

### 1.4.17

- Autoreload: php, twig, js, css and images trigger autoreload

### 1.4.16

- Autoreload: CSS and Javascript modification trigger autoreload

### 1.4.15

- Remove ACF json save
– Debug option true by default
- Whe debug is activate css and js are invalidated on reload by ?time=xxx

### 1.4.14

- Fix OB Flush warning
– Add ACF escaping code
– Fix RGPD Consent box
– Update hanako-ts to 1.2.2
– Fix PHP Warning due to update removal hook in menus

### 1.4.13

- Views structure update
- PHP 8
- Bugfix

### 1.4.12

- Minor renaming

### 1.4.11

- move Debug to views/ts/components
- add some function into Twig Functions (print_r)
- Image Layzloading !!!

### 1.4.10

- views structure update

### 1.4.9

- add responsive sizing class in Boostrap

### 1.4.8

- add Swiper.ts helper to handle swipe on touch devices

### 1.4.7

- package.json update and some polishing.

### 1.4.6

- Add comment disabling in backend options

### 1.4.5

- Add CSS font smoothing
- Remove compile .js
- Set Boostrap to 12 columns
- Fix models/single.php

### 1.4.4

- Update to Boostrap 5.1

### 1.4.3

- RGPD consent
- Translation files

### 1.4.2

- Enhenced redirection system for non logged user

### 1.4.1

- Bugfix in compile script

### 1.4.0

- Directory structure refactoring (again). The system now use a "MVC" inspired structure.

### 1.3.0

- Directory structure refactoring
- New webpack workflow

### 1.2.0

- Add Timmy support
- New install notices system

### 1.1.1

Various enhancement
- add Webpack entry point for editor-style.css
- update scss file structure
- add scss file for styling language selector
- update webpack config

### 1.1.0

Switch from global WP structure to theme only

### 1.0

Brand new system based on Timber/Twig, SCSS and Typescript
