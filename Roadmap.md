~~Strikethrough items~~ have been implemented.

## Use Google Maps ##
The metadata in QuickRoute jpeg files can be used to geocode DOMA maps. Save the essential geocoding (map corners, map center, etc) in the database.
  * ~~Show the location of a DOMA map on a GM map.~~
  * ~~Show the locations of multiple DOMA maps on a GM map~~
  * Use WorldOfO-style combination of thumbnail and overview map.
  * ~~Use GM polygons to show the border of a DOMA map.~~

## Google Earth integration ##
  * ~~Create a php class that generates a kmz file from a single DOMA map(s) on the fly.~~
  * Create a php class that generates a kmz file from multiple DOMA map(s) on the fly. _(will probably be hard since it will consume a lot of memory)_
  * ~~Every geocoded map should have a "Open in Google Earth" icon/link next to it.~~

## DOMA event object ##
There is a need to define events (i e trainings/competitions). An event is a container for a number of DOMA maps.
  * An event has a name, a date, ant a number of other attributes.

## DOMA map object enhancements ##
  * Make it possible to define and store custom attributes for a map.
  * ~~Add fields for QuickRoute jpeg metadata: straight line distance, time, etc.~~
  * Password-protected maps.
  * Maps visible only to the owner.
  * Further integration with QuickRoute jpeg metadata: calculate speed, heart rate, etc at a certain time.
  * ~~Maps might be encrypted before a certain time, which makes it possible to publish maps for a traning in advance without them being revealed to the public.~~

## Replay using javascript/SVG ##
Use the experimental replay from http://www.matstroeng.se/tracking/replay.php?eventId=4. Source code available on demand. Might be used for live tracking as well as post-race replay.

## Language translations ##
  * ~~Place language selection to the top bar (every page)~~
  * ~~Define available languages in config.php~~

## Map detail ##
  * 