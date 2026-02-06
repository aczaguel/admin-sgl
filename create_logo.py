from PIL import Image, ImageDraw, ImageFont

# Crear imagen base (768x768)
width, height = 768, 768
img = Image.new('RGB', (width, height), color='#1a3a52')
draw = ImageDraw.Draw(img)

# Dibujar círculos (simulación del logo)
center_x, center_y = 384, 290

# Círculo exterior cyan
for i in range(25):
    draw.ellipse([center_x-150-i, center_y-150-i, center_x+150+i, center_y+150+i], 
                 outline='#2eb8ce', width=1)

# Círculo interior azul
for i in range(15):
    draw.ellipse([center_x-120-i, center_y-120-i, center_x+120+i, center_y+120+i], 
                 outline='#1e6a8f', width=1)

# Texto SGL (usando font por defecto)
try:
    font_large = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 120)
    font_medium = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 80)
except:
    font_large = ImageFont.load_default()
    font_medium = ImageFont.load_default()

# Dibujar texto SGL
draw.text((384, 290), 'SGL', fill='white', font=font_large, anchor='mm')

# Dibujar texto Servicios
draw.text((384, 540), 'Servicios', fill='white', font=font_medium, anchor='mm')

# Guardar versión normal (fondo oscuro)
img.save('public/assets/vendors/images/logo_sgl_servicios.png')

# Crear versión blanca (fondo transparente)
img_white = Image.new('RGBA', (width, height), color=(0, 0, 0, 0))
draw_white = ImageDraw.Draw(img_white)

# Círculos en blanco/gris claro
for i in range(25):
    draw_white.ellipse([center_x-150-i, center_y-150-i, center_x+150+i, center_y+150+i], 
                       outline='#ffffff', width=1)
for i in range(15):
    draw_white.ellipse([center_x-120-i, center_y-120-i, center_x+120+i, center_y+120+i], 
                       outline='#e0e0e0', width=1)

draw_white.text((384, 290), 'SGL', fill='white', font=font_large, anchor='mm')
draw_white.text((384, 540), 'Servicios', fill='white', font=font_medium, anchor='mm')

img_white.save('public/assets/vendors/images/logo_sgl_servicios_white.png')

print("✓ Logos creados exitosamente")
print("  - logo_sgl_servicios.png (versión oscura)")
print("  - logo_sgl_servicios_white.png (versión clara)")
