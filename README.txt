This is a very simple Moodle filter utilizing the ChemDoodle web component to show
JCAMP-DX files (*.jdx) in the ChemDoodle web component.

Developed by Carl LeBlond - copyright  2014 onwards Carl LeBlond

TO INSTALL:

1. Unpack the .zip file you download.  You should obtain a folder called chemdoodle.
2. Copy the chemdoodle folder (and all its contents) to the filter folder
of your Moodle 2.3+ installation i.e. alongside the folders for other filters.
3. Activate the filter in Moodle's filter admin screen.


USAGE:

To add a spectra to your course simply create a link to a .jdx file.  The text of 
the link will be replaced by a ChemDoodle spectra canvas.

    1) Type some text in your content.
    2) Highlight the text with your mouse.
    3) Click the "Insert/Edit Link" icon.
    4) Click the "Browse" icon.
    5) Either upload your own by clicking "Upload your own" or choose from hundreds in 
       spectra database (NMR Spectra, IR_Spectra or MS_Spectra folders).


There are three possible spectra configurations possible;

    1) Perspective Canvas - With a ChemDoodle Perspective canvas you can zoom in and 
       adjust the scale of the spectrum. This is the default setting.

    2) Seeker canvas - The Seeker canvas allows the user to obtain or see the 
       coordinates of points on the spectrum. To use the Seeker canvas add ?c=2 at 
       the end of the URL when you create the link or by editing the link.

    3) Linked Perspective and Seeker Canvas - This option provides two linked spectrum. 
       To use both Perspective and Seeker, add ?c=3 to the link url.


Title control

You can also control the title. Adding ?c=2&title=none to the end of the link URL 
will setup a Seeker canvas with a blank title. This is useful when including 
spectrum in quiz questions. Adding ?title=NEW TITLE will override the existing 
title found in the jdx file. The default is to use the title from the jdx file.

Flipping the X-axis

Adding ?c=1&flipaxis=false to the end of the link URL will create a perspective 
canvas without a flipped axis (i.e. the scale will increase from left to right). 
The default setting is true (i.e. the x-axis is flipped)

x-axis max and min

Adding ?minx=4&maxx=6 will adjust the initial x-axis max and min scale of the spectrum.

Integration Line

The integration line can be turned off by adding ?int=off at end of URL. By default 
the integration is turned on.

The following is an examples illustrate the three possible ChemDoodle canvas options.  
With a ChemDoodle Perspective canvas you can zoom in and adjust the scale of the spectra.  
