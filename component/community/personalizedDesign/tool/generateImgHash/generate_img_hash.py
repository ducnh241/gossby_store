#!/usr/bin/env python3

from PIL import Image
import imagehash
from colorthief import ColorThief
import argparse
import os

parser = argparse.ArgumentParser(description='Process image to hash.')
# Add the arguments
parser.add_argument("--file", "-f", type=str, required=True, help= 'input image file path')

# Execute the parse_args() method
args = parser.parse_args()

input_path = args.file
size = 200, 200

if not os.path.isfile(input_path):
    raise RuntimeError('Input file path is not exist')

def to_safe_color(color):
    def normalize(n): 
        tmp = n % 51
        if tmp >  25: 
            tmp = n+51-tmp
        else :
            tmp = n-tmp
        return round(tmp / 17)

    (r, g, b) = map(normalize, color)
    return ('{:X}{:X}{:X}{:X}{:X}{:X}').format(r,r,g,g,b,b).lower()


if __name__ == "__main__":
    img = Image.open(input_path)
  
    hash = imagehash.average_hash(img)

    img.thumbnail(size, Image.ANTIALIAS)
    
    color_thief = ColorThief(img)
    palette = color_thief.get_palette(color_count=2 ,quality=1)
    safe_colors = map(to_safe_color, palette)

    full_hash = str(hash)
    for color in safe_colors:
        full_hash += '_' + color
        
    print(full_hash)

