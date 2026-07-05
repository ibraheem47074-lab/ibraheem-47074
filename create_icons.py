# Simple icon creator script
import os

# Create a simple SVG for the icon
svg_content = '''<?xml version="1.0" encoding="UTF-8"?>
<svg width="{size}" height="{size}" viewBox="0 0 {size} {size}" xmlns="http://www.w3.org/2000/svg">
  <circle cx="{half}" cy="{half}" r="{radius}" fill="#dc3545"/>
  <text x="{half}" y="{half}" text-anchor="middle" dominant-baseline="middle" 
        font-family="Arial" font-size="{font_size}" fill="white" font-weight="bold">PK</text>
</svg>'''

# Create icons directory
icons_dir = r'd:\Xampp\htdocs\PK-LIVE NEWS\assets\images\icons'
os.makedirs(icons_dir, exist_ok=True)

# Icon sizes to create
sizes = [192, 144, 32, 16]

for size in sizes:
    # Generate SVG content
    half = size // 2
    radius = half - (size // 8)
    font_size = size // 3
    
    svg = svg_content.format(
        size=size, 
        half=half, 
        radius=radius, 
        font_size=font_size
    )
    
    # Save as SVG file (fallback)
    svg_filename = f'icon-{size}x{size}.svg'
    svg_path = os.path.join(icons_dir, svg_filename)
    with open(svg_path, 'w') as f:
        f.write(svg)
    
    print(f'Created {svg_filename}')

print('SVG icons created as fallback!')
