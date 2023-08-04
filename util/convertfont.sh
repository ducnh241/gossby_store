#!/bin/bash

#INSIDE WSL need install dos2unix
#apt-get install dos2unix
#dos2unix sh_file_path

fontforge -c "import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.generate(argv[2])" $1 $2
