# ChangeLog

## 2.8.0
* ADD: Advanced customization of booking rules;
* ADD: Creating a related order when creating a booking through the admin panel;
* ADD: Filtering and sorting functionality for the list of bookings in the admin panel;
* ADD: Two-way WooCommerce order sync;
* ADD: Admin datepicker compatibility with general `Week Starts On` setting.
* UPD: Some WooCommerce related options display;
* UPD: Default DB columns, remove unused options; 
* FIX: Days Off items titles with special chars;
* FIX: Related order post type creation with different post type supports settings;
* FIX: Apartment Booking action WooCommerce order details Booking Instance display name issue;
* FIX: WooCommerce related order Booking details view while edit info;
* FIX: Edit Calendars popup handling.

## 2.7.2
* ADD: Edit & delete functionalities to details popup;
* UPD: Better WPML/Polylang compatibility;
* FIX: JetEngine forms WC setup popup view;
* FIX: Reselect date range when editing booking;
* FIX: Proper units display after booking item editing;
* FIX: PHP 8.2 deprecation errors.

## 2.7.1
* ADD: Ability to edit units in admin booking area;
* ADD: Booking Availability Calendar widget searched dates display;
* ADD: Compatibility with new WooCommerce REST API checkout;
* FIX: Searched dates with the checkout only option;
* FIX: Booking Availability Calendar widget clear selection;
* FIX: Units count dynamic tag for instance without units;
* FIX: Dynamic tags fallback;
* FIX: Order meta error output.

## 2.7.0
* ADD: iCal templated editor;
* ADD: Compatibility with some core JetEngine macros;
* ADD: Remove calendar import field functionality;
* UPD: Better compatibility with WPML plugin, custom label translations;
* UPD: Booking JS methods globally accessible;
* FIX: iCal sync log appearance;
* FIX: Filters storage type call;
* FIX: Dynamic tags functionality with Bookings Availability widget;
* FIX: Saving iCal import URls;
* FIX: Calendars section popups view;

## 2.6.3
* ADD: Integration with Woocommerce High-Performance Order Storage;
* ADD: Elementor 3.10 compatibility with custom size unit;
* ADD: Polylang compatibility;
* ADD: Some global date picker configuration to individual bookable object;
* ADD: JetPlugins library;
* ADD: `'jet-booking.input.config'` and `'jet-booking.date-range-picker.date-show-params'` JS hooks in admin area date picker;
* ADD: `'jet-booking.date-range-picker.date-show-params'` and `'jet-booking.date-range-picker.disabled-day'` JS hooks for additional control over disable dates;
* UPD: JS hooks usage due to JetPlugins library;
* FIX: Order details info display in cart and checkout pages;
* FIX: Date picker value sync;

## 2.6.2
* ADD: Option in form action to disable WC integration;
* UPD: Booking info popup UI improvements;
* FIX: Nonce validation;
* FIX: Days Off option fallback value;
* FIX: Last Day removing in Days Off.

## 2.6.1.1
* FIX: `%ADVANCED_PRICE%` macros functionality.

## 2.6.1
* FIX: Date range filter for `checkin_checkout` query variable;
* FIX: Booking form date picking in JetPopup;
* FIX: Elementor Pro Popup date picker field issue.

## 2.6.0
* ADD: `'jet-booking/rest-api/bookings-list/bookings'` hook to control bookings display in admin area;
* ADD: `min_days`, `max_days`, `start_day_offset` date picker options;
* UPD: Reorganise settings pages;
* UPD: Date picker script refactor;
* UPD: Get value from single field for the date picker calendar;
* UPD: Booking Available Calendar widget controls;
* FIX: Elementor editor preview error;
* FIX: Advanced price value.

## 2.5.5
* ADD: Some WPML compatibility;
* UPD: Datepicker field templates;
* FIX: Calendar proper label in tooltip;
* FIX: Excluded Dates option handle in script and admin area datepicker;
* FIX: Default and filtered datepicker field values;
* FIX: Weeks offset functionality;
* FIX: Booking functionality for different languages;

## 2.5.4
* ADD: `jquery-date-range-picker` in to dashboard edit & add booking popup;
* ADD: `'jet-booking/google-calendar-url/utc-timezone'` hook for timezone manipulation in Google calendar event link;
* ADD: `'jet-booking/form-fields/check-in-out/default-value'` hook for default `check-in-out` field value;
* UPD: Booking admin popups templates;
* FIX: Advanced price rates default value.

## 2.5.3
* ADD: JS filter `'jet-booking/calendar/config'` for calendar widget config;
* ADD: JS filter `'jet-booking/apartment-price'` for apartment price;
* FIX: Booking calendar layout;

## 2.5.2
* ADD: Dynamic tags: Available units count, Bookings count;
* ADD: Additional custom labels;
* UPD: Allow filtering settings value before return with `'jet-booking/settings/get/' . $setting-key`;
* UPD: Custom labels default value initialization;
* FIX: Order of advanced prices application;
* FIX: `check-in-out` field searched dates;
* FIX: Dynamic tag price per day/night;
* FIX: Advanced price popups data duplication;
* FIX: Filter result with Checkout only option.

## 2.5.1
* ADD: Checkout only days option;
* ADD: `jet-booking/form-action/pre-process` hook to allow handle booking from 3rd party plugin or theme;
* UPD: Update error message in admin popups;
* FIX: Overlapping bookings issue while update booking in admin area;
* FIX: Price rates popups overlays;
* FIX: JetEngine form while plugin setup;
* FIX: Booking list pagination;
* FIX: Minor WooCommerce integration errors;
* FIX: Compatibility with Elementor 3.7.

## 2.5.0
* ADD: Creating booking from admin area;
* ADD: Days off functionality;
* ADD: Disable weekdays and weekends functionality;
* UPD: Admin Booking page popups;
* FIX: One day booking seasonal price;
* FIX: iCal sync wrong check out date;
* FIX: Searched dates display in date fields with One day booking option;
* FIX: Admin Calendar page styles.

## 2.4.6
* FIX: minor JS/PHP issue

## 2.4.5
* FIX: Per Day booking type same dateCheck-in and Check-out.

## 2.4.4
* ADD: Cookies filters searched date store type;
* UPD: WooCommerce order booking details in admin area;
* FIX: Seasonal prising empty rates issue;
* FIX: Booking apartment unit ID;
* FIX: Cron iCal interval synchronization;
* FIX: Default WC product ordering with JetBooking integration;
* FIX: JetBooking dynamic tags;
* FIX: Date range filed in popup after ajax call;
* FIX: Items with units booked dates using per day booking period;
* FIX: Edit&Details popups view in booking list page;
* FIX: Calendar widget editor render;
* FIX: Session filters searched date store type.

## 2.4.3
* FIX: apply units;
* FIX: returning a string instead of output;
* FIX: get_booked_apartments ignore apartments with invalid statuses;
* FIX: Elementor 3.6 compatibility.

## 2.4.2
* FIX: First day of the week

## 2.4.1
* FIX: Translation strings
* FIX: Seasonal prices without post editor

## 2.4.0
* ADD: Seasonal prices

## 2.3.5
* FIX:Synchronizing calendars

## 2.3.4
* FIX:Error of check-in-out fields when submitting a form

## 2.3.3
* FIX: JetFormBuilder compatibility

## 2.3.2
* FIX: Price per 1 day/night

## 2.3.1
* FIX: iCal compatibility

## 2.3.0
* ADD: JetFormBuilder plugin compatibility

## 2.2.6
* FIX: Display of booked days in the calendar

## 2.2.5
* FIX: check in - check out field

## 2.2.4
* ADD: Default apartment price value
* FIX: Booking Availability Calendar

## 2.2.3
* FIX: Init check-out field

## 2.2.2
* FIX: Placeholder in check-out field
* FIX: Option per nights. When the option is enabled, 1 day cannot be booked as the beginning and end of the booking
* FIX: Fixed calendar on mobile device
* FIX: Plugin localization

## 2.2.1
* FIX: Check-in/check-out field in booking form

## 2.2.0
* FIX: iCal post count
* ADD: Select the first day of the week
* ADD: compatibility with php 5.6 +

## 2.1.2
* UPD: Added localization file

## 2.1.1
* FIX: WC product creation.

## 2.1.0
* ADD: Added Booking Availability Calendar widget;
* ADD: Allow to add booking details to WooCommerce checkout fields;
* ADD: Added Property Rates Based on the length of stay;
* ADD: Allow to add booking details to WooCommerce orders;
* ADD: Allow ability for users to add a booking to their calendar.
* FIX: Fixed minor bugs.
