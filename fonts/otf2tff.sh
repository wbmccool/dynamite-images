#!/usr/local/bin/fontforge
# Quick and dirty hack: converts a font to truetype (.ttf)
# see http://www.stuermer.ch/blog/convert-otf-to-ttf-font-on-ubuntu.html
Print("Opening "+$1);
Open($1);
Print("Saving "+$1:r+".ttf");
Generate($1:r+".ttf");
Quit(0);