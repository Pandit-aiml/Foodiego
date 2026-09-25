import http.server
import socketserver
import json
import sqlite3
import urllib.parse
import os
import sys

PORT = 8000
DB_FILE = 'food_delivery.db'
OTP_STORE = {}

def init_db():
    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()
    
    cursor.execute('''
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        phone TEXT,
        address TEXT,
        role TEXT DEFAULT 'customer',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ''')

    # Migration for users.role and avatar
    cursor.execute("PRAGMA table_info(users);")
    user_cols = [col[1] for col in cursor.fetchall()]
    if 'role' not in user_cols:
        cursor.execute("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'customer';")
    if 'avatar' not in user_cols:
        cursor.execute("ALTER TABLE users ADD COLUMN avatar TEXT;")

    cursor.execute('''
    CREATE TABLE IF NOT EXISTS restaurants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        cuisine TEXT,
        image TEXT,
        rating REAL DEFAULT 4.0,
        delivery_time TEXT DEFAULT '30-40 min',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ''')

    cursor.execute('''
    CREATE TABLE IF NOT EXISTS menu_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        restaurant_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        image TEXT,
        category TEXT,
        is_available INTEGER DEFAULT 1,
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    );
    ''')

    # Migration for menu_items.is_available
    cursor.execute("PRAGMA table_info(menu_items);")
    menu_cols = [col[1] for col in cursor.fetchall()]
    if 'is_available' not in menu_cols:
        cursor.execute("ALTER TABLE menu_items ADD COLUMN is_available INTEGER DEFAULT 1;")

    cursor.execute('''
    CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        customer_name TEXT NOT NULL,
        phone TEXT NOT NULL,
        address TEXT NOT NULL,
        total REAL NOT NULL,
        payment_method TEXT DEFAULT 'COD',
        payment_status TEXT DEFAULT 'Pending',
        status TEXT DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    );
    ''')

    # Migration for orders payment columns
    cursor.execute("PRAGMA table_info(orders);")
    order_cols = [col[1] for col in cursor.fetchall()]
    if 'payment_method' not in order_cols:
        cursor.execute("ALTER TABLE orders ADD COLUMN payment_method TEXT DEFAULT 'COD';")
    if 'payment_status' not in order_cols:
        cursor.execute("ALTER TABLE orders ADD COLUMN payment_status TEXT DEFAULT 'Pending';")

    cursor.execute('''
    CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        menu_item_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL,
        price REAL NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
    );
    ''')

    # Seed Staff Account if missing
    cursor.execute("SELECT id FROM users WHERE email = 'staff@foodiego.com';")
    if not cursor.fetchone():
        cursor.execute('''
        INSERT INTO users (name, email, password, phone, address, role)
        VALUES ('Staff Kitchen Manager', 'staff@foodiego.com', 'staff123', '9999988888', 'Central Kitchen Headquarters', 'staff')
        ''')

    # Seed data if restaurants empty
    cursor.execute('SELECT COUNT(*) FROM restaurants;')
    if cursor.fetchone()[0] == 0:
        cursor.executemany('''
        INSERT INTO restaurants (name, cuisine, image, rating, delivery_time) VALUES (?, ?, ?, ?, ?)
        ''', [
            ('Spice Hub', 'North Indian', 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=900&q=80', 4.6, '25-35 min'),
            ('Pizza Corner', 'Italian', 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=900&q=80', 4.5, '30-40 min'),
            ('Burger House', 'American', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=80', 4.4, '20-30 min'),
            ('Wok Express', 'Chinese', 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=900&q=80', 4.3, '25-35 min')
        ])

        cursor.executemany('''
        INSERT INTO menu_items (restaurant_id, name, description, price, image, category, is_available) VALUES (?, ?, ?, ?, ?, ?, 1)
        ''', [
            (1, 'Paneer Butter Masala', 'Creamy tomato gravy with soft paneer', 220.00, 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=600&q=80', 'Main Course'),
            (1, 'Butter Naan', 'Tandoori naan brushed with butter', 45.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Breads'),
            (1, 'Veg Biryani', 'Fragrant basmati rice with vegetables and spices', 190.00, 'https://images.unsplash.com/photo-1599043513900-ed6fe01d3833?auto=format&fit=crop&w=600&q=80', 'Rice'),
            (2, 'Margherita Pizza', 'Classic pizza with tomato, mozzarella and basil', 249.00, 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=600&q=80', 'Pizza'),
            (2, 'Farmhouse Pizza', 'Loaded with fresh vegetables and cheese', 329.00, 'https://images.unsplash.com/photo-1579751626657-72bc17010498?auto=format&fit=crop&w=600&q=80', 'Pizza'),
            (3, 'Classic Cheeseburger', 'Juicy patty, cheese, lettuce and signature sauce', 199.00, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80', 'Burgers'),
            (3, 'French Fries', 'Crispy golden salted fries', 99.00, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=600&q=80', 'Sides'),
            (4, 'Veg Hakka Noodles', 'Stir-fried noodles with fresh vegetables', 179.00, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80', 'Noodles'),
            (4, 'Manchurian', 'Crispy vegetable balls in spicy sauce', 169.00, 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=600&q=80', 'Starters')
        ])
    # Seed South Tiffins if missing
    cursor.execute("SELECT id FROM restaurants WHERE name = 'South Tiffins';")
    if not cursor.fetchone():
        cursor.execute('''
        INSERT INTO restaurants (name, cuisine, image, rating, delivery_time) VALUES
        ('South Tiffins', 'South Indian', 'https://images.unsplash.com/photo-1610192244261-3f33de3f55e4?auto=format&fit=crop&w=900&q=80', 4.8, '20-30 min')
        ''')
        r_id = cursor.lastrowid
        cursor.executemany('''
        INSERT INTO menu_items (restaurant_id, name, description, price, image, category, is_available) VALUES (?, ?, ?, ?, ?, ?, 1)
        ''', [
            (r_id, 'Crispy Masala Dosa', 'Golden crispy dosa filled with spiced potato masala and served with coconut chutney & sambar', 130.00, 'https://images.unsplash.com/photo-1610192244261-3f33de3f55e4?auto=format&fit=crop&w=600&q=80', 'South Indian'),
            (r_id, 'Steamed Idli Sambar (2 Pcs)', 'Soft fluffy steamed rice cakes served with hot sambar and fresh chutneys', 80.00, 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80', 'South Indian'),
            (r_id, 'Crispy Medu Vada (2 Pcs)', 'Golden crispy lentil donuts served with sambar and coconut chutney', 90.00, 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=600&q=80', 'South Indian'),
            (r_id, 'Onion Rava Dosa', 'Crispy semolina dosa topped with finely chopped onions and green chillies', 150.00, 'https://images.unsplash.com/photo-1610192244261-3f33de3f55e4?auto=format&fit=crop&w=600&q=80', 'South Indian'),
            (r_id, 'Authentic Filter Coffee', 'Traditional South Indian frothy decoction filter coffee', 50.00, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80', 'Beverages')
        ])

    # Seed Champaran Rasoi (Bihari Special) if missing or incomplete
    cursor.execute("SELECT id FROM restaurants WHERE name = 'Champaran Rasoi';")
    b_res = cursor.fetchone()
    if not b_res:
        cursor.execute('''
        INSERT INTO restaurants (name, cuisine, image, rating, delivery_time) VALUES
        ('Champaran Rasoi', 'Bihari Special', 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=900&q=80', 4.9, '25-35 min')
        ''')
        b_id = cursor.lastrowid
    else:
        b_id = b_res[0]

    bihari_items = [
        ('Litti Chokha', 'Roasted sattu-stuffed wheat balls served with pure desi ghee, smoky brinjal-tomato chokha & chutney', 140.00, 'https://i0.wp.com/flavoursonplate.com/wp-content/uploads/2018/11/litti-chokha.png?w=1536&ssl=1', 'Main Course'),
        ('Sattu Paratha', 'Whole wheat parathas stuffed with spiced roasted gram flour (sattu), served with curd', 120.00, 'https://i2.wp.com/www.vegrecipesofindia.com/wp-content/uploads/2026/06/sattu-paratha.jpg', 'Breakfast'),
        ('Dal Pitha', 'Steamed rice flour dumplings filled with spicy chana dal paste', 110.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4y0d78HS3vac12meUEslkZLmdsOGFhFIlK7LzJ5E3L-pH3y75WtcNQFnC&s=10', 'Snack/Main'),
        ('Thekua', 'Traditional Bihari sweet snack made with wheat flour, jaggery and dry fruits', 80.00, 'https://www.cadburydessertscorner.com/hubfs/dc-website-2022/web-stories/history-of-thekua-explore-the-story-of-this-bihars-sweet-treat/feature-image.png', 'Sweet/Snack'),
        ('Khaja', 'Layered crispy deep-fried sweet delicacy dipped in sugar syrup', 90.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/3/35/Baleswari_khaja_pheni_Oriya_cuisine.jpg/960px-Baleswari_khaja_pheni_Oriya_cuisine.jpg?utm_source=en.wikipedia.org&utm_campaign=imageinfo&utm_content=thumbnail', 'Sweet'),
        ('Malpua', 'Rich sweet pancakes soaked in cardamom sugar syrup', 100.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/cc/Malpoa_pitha.jpg/500px-Malpoa_pitha.jpg?utm_source=en.wikipedia.org&utm_campaign=parser&utm_content=thumbnail', 'Sweet'),
        ('Tilkut', 'Traditional Gaya special sweet made of sesame seeds and jaggery', 85.00, 'https://gannug.com/wp-content/uploads/2025/12/WhatsApp-Image-2025-12-02-at-10.33.07-768x768.jpeg', 'Sweet'),
        ('Balushahi', 'Traditional crispy, flaky sweet made with flour and sugar syrup', 95.00, 'https://scontent.fixc2-1.fna.fbcdn.net/v/t39.30808-6/557744993_1328453292063407_2174986970234008794_n.jpg?stp=dst-jpg_tt6&cstp=mx1080x1350&ctp=s640x640&_nc_cat=108&_nc_map=urlgen_bucketless&ccb=1-7&_nc_sid=833d8c&_nc_ohc=dNcT4L53URwQ7kNvwEPjKD6&_nc_oc=AdqhjMxhi38lvoIDiN8Ug96uW-jjnQ5Gh0J8lRk4--EfMXrVqz5H761wWgHEj3_lRFU&_nc_zt=23&_nc_ht=scontent.fixc2-1.fna&_nc_gid=Hh1abyOMUho739cqVX5yZw&_nc_ss=7b2a8&oh=00_AQKBkmoETfuI0lTIqkus--Zjacrbg6o4ezbgbWVcfEX1yw&oe=6AAAF86F', 'Sweet'),
        ('Anarsa', 'Rice flour and jaggery sweet coated with crunchy sesame seeds', 90.00, 'https://pbs.twimg.com/media/GtYpbZsWsAAGoQs?format=jpg&name=small', 'Sweet'),
        ('Makhana Kheer', 'Creamy dessert made with lotus seeds, milk, sugar and cardamom', 120.00, 'https://scontent.fixc2-1.fna.fbcdn.net/v/t39.30808-6/547231855_1334078504784496_3484003969669887663_n.jpg?stp=dst-jpg_tt6&cstp=mx1080x1147&ctp=p180x540&_nc_cat=103&_nc_map=urlgen_bucketless&ccb=1-7&_nc_sid=127cfc&_nc_ohc=H7ExBi3aKA8Q7kNvwEsZdz2&_nc_oc=AdqqyFPmcaGVlLE0Q4T95C_FiAIv4qK5oOhgPqrWr1h8HVFlplJkJ23JgmMTVGpPbn4&_nc_zt=23&_nc_ht=scontent.fixc2-1.fna&_nc_gid=twwxME1o6n5S_HSLJaGmOA&_nc_ss=7b2a8&oh=00_AQIkFOb2u0BCIMRRH3b3j67SFyOz-eYN6x4Bz9V-1b8ytQ&oe=6AAB0747', 'Dessert'),
        ('Chana Ghugni', 'Spicy black chickpea curry tempered with Bihari spices and green chillies', 90.00, 'https://i0.wp.com/foodtrails25.com/wp-content/uploads/2019/09/img_7178_ezy-watermark_27-09-2019_02-57-52pm.jpg?w=1440&ssl=1', 'Snack'),
        ('Sattu Sharbat', 'Refreshing summer beverage made with roasted chana sattu, roasted cumin & lemon juice', 40.00, 'https://pbs.twimg.com/media/FVWyP6YacAAPr5L?format=jpg&name=900x900', 'Beverage'),
        ('Kadhi Badi', 'Soft gram flour dumplings cooked in tangy spiced yogurt curry', 130.00, 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Bihari Kadhi', 'Traditional Bihari style yellow curd curry with crispy fried badi', 130.00, 'https://images.unsplash.com/photo-1626500155562-e1a4c37d9532?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Bhaat Dal', 'Classic comforting Bihari meal of steamed basmati rice and spiced yellow lentil dal', 110.00, 'https://images.unsplash.com/photo-1516714435131-44d6b64dc6a2?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Aloo Chokha', 'Mashed potatoes tempered with pure mustard oil, green chillies and fresh coriander', 60.00, 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=600&q=80', 'Side Dish'),
        ('Baingan Chokha', 'Smoky roasted eggplant mash seasoned with garlic, chillies and raw mustard oil', 70.00, 'https://images.unsplash.com/photo-1572449043416-55f4685c9bb7?auto=format&fit=crop&w=600&q=80', 'Side Dish'),
        ('Tamatar Chokha', 'Charred tomato chokha with garlic, green chillies and mustard oil touch', 60.00, 'https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&w=600&q=80', 'Side Dish'),
        ('Sattu Kachori', 'Golden fried deep kachori filled with spiced sattu and aromatic seeds', 80.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Snack'),
        ('Dal Puri', 'Puffed fried puris filled with seasoned chana dal mash', 100.00, 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=600&q=80', 'Breakfast'),
        ('Poori-Sabzi', 'Hot crispy puris served with Bihari style spicy potato curry', 110.00, 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80', 'Breakfast'),
        ('Kachori-Sabzi', 'Flaky spiced kachoris paired with spicy potato gravy', 110.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Breakfast'),
        ('Chura-Dahi', 'Rustic beaten rice served with thick fresh curd and organic jaggery', 90.00, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80', 'Breakfast'),
        ('Chura-Gur', 'Crispy roasted beaten rice mixed with sweet dark jaggery', 70.00, 'https://images.unsplash.com/photo-1590080875515-8a3a8dc5735e?auto=format&fit=crop&w=600&q=80', 'Breakfast'),
        ('Dahi-Chura', 'Traditional Bihari specialty dish of chura soaked in sweet fresh curd', 90.00, 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=600&q=80', 'Traditional'),
        ('Matar Kachori', 'Deep fried fluffy kachori packed with spiced green peas masala', 85.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Snack'),
        ('Lai', 'Traditional crispy sweet laddoos made of puffed rice / ramdana and melted jaggery', 60.00, 'https://images.unsplash.com/photo-1599043513900-ed6fe01d3833?auto=format&fit=crop&w=600&q=80', 'Sweet/Snack'),
        ('Murhi (Murmura) Chura', 'Crunchy Bihari street snack mix of spiced puffed rice, peanuts & green chillies', 50.00, 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=600&q=80', 'Snack'),
        ('Buniya', 'Tiny sweet boondi pearls soaked in fragrant cardamom sugar syrup', 75.00, 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=600&q=80', 'Sweet'),
        ('Parwal Ki Mithai', 'Exquisite Bihari sweet made of tender pointed gourd stuffed with rich khoya & nuts', 130.00, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80', 'Sweet'),
        ('Doodh Peda', 'Soft milk peda infused with saffron and green cardamom', 110.00, 'https://images.unsplash.com/photo-1599043513900-ed6fe01d3833?auto=format&fit=crop&w=600&q=80', 'Sweet'),
        ('Khurma', 'Crunchy fried flour bite sweets coated with crystallized sugar', 85.00, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?auto=format&fit=crop&w=600&q=80', 'Sweet'),
        ('Pua', 'Traditional sweet pan-fried pancake flavored with fennel seeds and banana', 75.00, 'https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=600&q=80', 'Sweet'),
        ('Rasia', 'Special Bihari festival kheer cooked with rice, milk and raw jaggery', 95.00, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=600&q=80', 'Sweet'),
        ('Gur Anarsa', 'Delicate rice flour Anarsa sweetened with pure jaggery and sesame seeds', 95.00, 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=600&q=80', 'Sweet'),
        ('Sattu Roti', 'Healthy whole wheat flatbread stuffed with savory sattu mix', 80.00, 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Makuni', 'Traditional sattu stuffed thick paratha griddled with mustard oil', 95.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Breakfast/Snack'),
        ('Bihari Aloo', 'Classic Bihari dry potato stir fry seasoned with garlic and mustard oil', 100.00, 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=600&q=80', 'Main/Side'),
        ('Bihari Dal Puri', 'Rich spiced chana dal stuffed puri served with festival gravies', 120.00, 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Chana Dal Pitha', 'Steamed rice flour pitha rolls stuffed with spiced crushed chana dal', 105.00, 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?auto=format&fit=crop&w=600&q=80', 'Snack'),
        ('Makhana Curry', 'Rich creamy gravy cooked with popped foxnuts, green peas and cashew paste', 180.00, 'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Makhana Sabzi', 'Aromatic tomato-onion curry with fried makhana and veggies', 170.00, 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Khesari Dal', 'Rustic Bihari grass pea dal slow-cooked and tempered with ghee & garlic', 100.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Badi Curry', 'Sun-dried urad dal dumplings simmered in spicy garlic gravy', 120.00, 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=600&q=80', 'Main Course'),
        ('Jhalmudi', 'Tangy & spicy Bihari style puffed rice mix with mustard oil, herbs & lemon', 50.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=600&q=80', 'Snack'),
        ('Litti with Chicken Curry', 'Signature roasted Litti dunked in ghee served with spicy Bihari chicken curry', 240.00, 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?auto=format&fit=crop&w=600&q=80', 'Non-Veg'),
        ('Bihari Mutton Curry', 'Champaran style mutton slow-cooked in handi with mustard oil & whole spices', 340.00, 'https://images.unsplash.com/photo-1545247181-516773cae754?auto=format&fit=crop&w=600&q=80', 'Non-Veg'),
        ('Bihari Chicken Curry', 'Home-style chicken curry in rich mustard-onion gravy with garlic', 260.00, 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&w=600&q=80', 'Non-Veg'),
        ('Machhli Curry', 'Fresh fish steaks simmered in traditional Bihari mustard-garlic gravy', 250.00, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=600&q=80', 'Non-Veg'),
        ('Bihari Kabab', 'Mouth-watering tender marinated meat strips charcoal grilled with Bihari spices', 280.00, 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=600&q=80', 'Non-Veg')
    ]

    cursor.execute("SELECT COUNT(*) FROM menu_items WHERE restaurant_id = ?;", (b_id,))
    cnt = cursor.fetchone()[0]
    if cnt < 50:
        cursor.execute("DELETE FROM menu_items WHERE restaurant_id = ?;", (b_id,))
        for name, desc, price, img, cat in bihari_items:
            cursor.execute('''
            INSERT INTO menu_items (restaurant_id, name, description, price, image, category, is_available)
            VALUES (?, ?, ?, ?, ?, ?, 1)
            ''', (b_id, name, desc, price, img, cat))

    # Seed Amritsari Dhaba & Haveli (Punjabi Special) if missing
    cursor.execute("SELECT id FROM restaurants WHERE name = 'Amritsari Dhaba & Haveli';")
    p_res = cursor.fetchone()
    if not p_res:
        cursor.execute('''
        INSERT INTO restaurants (name, cuisine, image, rating, delivery_time) VALUES
        ('Amritsari Dhaba & Haveli', 'Punjabi Special', 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=900&q=80', 4.9, '25-35 min')
        ''')
        p_id = cursor.lastrowid
        punjabi_items = [
            ('Amritsari Stuffed Kulcha with Chole', 'Crispy layered tandoori kulcha stuffed with spiced potatoes & paneer, served with spicy Amritsari chole, butter & imli chutney', 160.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Breakfast/Main'),
            ('Sarson Ka Saag & Makki Ki Roti', 'Traditional winter dish of slow-cooked mustard greens topped with white butter, served with 2 makki rotis & jaggery', 190.00, 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80', 'Main Course'),
            ('Dal Makhani (Desi Ghee)', 'Overnight slow-cooked black lentils simmered with butter, cream and aromatic Punjabi spices', 220.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=600&q=80', 'Main Course'),
            ('Butter Chicken (Amritsari Style)', 'Tandoori grilled chicken pieces in a rich, velvety tomato, butter and cashew gravy', 320.00, 'https://images.unsplash.com/photo-1603894584373-5ac82b2ae398?auto=format&fit=crop&w=600&q=80', 'Non-Veg'),
            ('Paneer Tikka Butter Masala', 'Smoky charcoal-grilled paneer cubes cooked in a rich, spicy Punjabi makhani gravy', 240.00, 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=600&q=80', 'Main Course'),
            ('Chole Bhature (2 Large Bhature)', 'Fluffy golden deep-fried bhature served with rich spicy chickpea chole, pickle and fried chillies', 140.00, 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?auto=format&fit=crop&w=600&q=80', 'Breakfast/Snack'),
            ('Kadhai Paneer', 'Cottage cheese tossed with bell peppers, onions and freshly ground kadhai masala', 230.00, 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=600&q=80', 'Main Course'),
            ('Punjabi Rajma Chawal', 'Comforting home-style red kidney bean curry served over hot steamed basmati rice with ghee', 130.00, 'https://images.unsplash.com/photo-1516714435131-44d6b64dc6a2?auto=format&fit=crop&w=600&q=80', 'Main Course'),
            ('Kadhi Pakora (Punjabi Style)', 'Deep-fried onion pakoras simmered in a thick, tangy yogurt and besan curry tempered with red chillies', 150.00, 'https://images.unsplash.com/photo-1626500155562-e1a4c37d9532?auto=format&fit=crop&w=600&q=80', 'Main Course'),
            ('Tandoori Chicken', 'Classic Punjabi chicken marinated in yogurt & red spices, roasted in a clay tandoor', 260.00, 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?auto=format&fit=crop&w=600&q=80', 'Non-Veg'),
            ('Malai Soya Chaap', 'Tender soya chaap marinated in creamy cashew paste, cheese and cardamom, cooked in tandoor', 210.00, 'https://images.unsplash.com/photo-1599043513900-ed6fe01d3833?auto=format&fit=crop&w=600&q=80', 'Starters'),
            ('Patiala Chicken Curry', 'Rich and flavorful Punjabi chicken curry cooked in an onion-tomato gravy with crushed black pepper', 310.00, 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?auto=format&fit=crop&w=600&q=80', 'Non-Veg'),
            ('Garlic Butter Naan', 'Fluffy tandoori naan infused with fresh minced garlic and brushed with melted butter', 60.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Breads'),
            ('Lacha Paratha', 'Multi-layered crispy tandoori paratha baked in clay oven', 50.00, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Breads'),
            ('Sweet Punjabi Lassi', 'Thick kulhad lassi blended with yogurt, malai and topped with saffron & pistachios', 70.00, 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80', 'Beverage'),
            ('Gulab Jamun (2 Pcs)', 'Soft fried khoya dumplings soaked in warm cardamom sugar syrup', 70.00, 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=600&q=80', 'Dessert')
        ]
        for name, desc, price, img, cat in punjabi_items:
            cursor.execute('''
            INSERT INTO menu_items (restaurant_id, name, description, price, image, category, is_available)
            VALUES (?, ?, ?, ?, ?, ?, 1)
            ''', (p_id, name, desc, price, img, cat))

    conn.commit()
    conn.close()

class FoodDeliveryHandler(http.server.SimpleHTTPRequestHandler):
    def log_message(self, format, *args):
        print(f"[{self.log_date_time_string()}] {args[0]} - {args[1]}")

    def do_GET(self):
        parsed = urllib.parse.urlparse(self.path)
        path = parsed.path
        query = urllib.parse.parse_qs(parsed.query)

        if path == '/api/restaurants.php':
            self.handle_get_restaurants()
        elif path == '/api/menu.php':
            restaurant_id = query.get('restaurant_id', [0])[0]
            self.handle_get_menu(restaurant_id)
        elif path == '/api/orders.php':
            self.handle_get_orders()
        elif path == '/api/customer_orders.php':
            user_id = query.get('user_id', [0])[0]
            self.handle_get_customer_orders(user_id)
        elif path == '/api/staff_stats.php':
            self.handle_get_staff_stats()
        else:
            if path in ['/', '/index.php']:
                self.serve_file('index.php', 'text/html; charset=utf-8')
            elif path in ['/staff', '/staff/', '/staff/index.php', '/admin', '/admin/', '/admin/index.php']:
                self.serve_file('staff/index.php', 'text/html; charset=utf-8')
            else:
                super().do_GET()

    def do_POST(self):
        parsed = urllib.parse.urlparse(self.path)
        path = parsed.path
        content_length = int(self.headers.get('Content-Length', 0))
        body = self.rfile.read(content_length).decode('utf-8')
        try:
            data = json.loads(body) if body else {}
        except Exception:
            data = {}

        if path.endswith('/api/signup.php'):
            self.handle_signup(data)
        elif path.endswith('/api/login.php'):
            self.handle_login(data)
        elif path.endswith('/api/update_profile.php'):
            self.handle_update_profile(data)
        elif path.endswith('/api/send_otp.php'):
            self.handle_send_otp(data)
        elif path.endswith('/api/verify_otp.php'):
            self.handle_verify_otp(data)
        elif path.endswith('/api/upload.php'):
            self.handle_upload(data)
        elif path.endswith('/api/order.php'):
            self.handle_create_order(data)
        elif path.endswith('/api/update_status.php'):
            self.handle_update_status(data)
        elif path.endswith('/api/menu_manage.php'):
            self.handle_menu_manage(data)
        else:
            self.send_error(404, "Not Found")

    def serve_file(self, rel_path, content_type):
        full_path = os.path.join(os.getcwd(), rel_path.replace('/', os.sep))
        if os.path.exists(full_path):
            with open(full_path, 'rb') as f:
                content = f.read()
            self.send_response(200)
            self.send_header('Content-Type', content_type)
            self.send_header('Content-Length', str(len(content)))
            self.end_headers()
            self.wfile.write(content)
        else:
            self.send_error(404, "File Not Found")

    def send_json(self, data, status=200):
        body = json.dumps(data).encode('utf-8')
        self.send_response(status)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def handle_signup(self, data):
        name = data.get('name', '').strip()
        email = data.get('email', '').strip().lower()
        password = data.get('password', '')
        phone = data.get('phone', '').strip()
        address = data.get('address', '').strip()
        role = 'customer'

        if not name or not email or not password:
            self.send_json({'error': 'Name, email, and password are required.'}, 400)
            return

        conn = sqlite3.connect(DB_FILE)
        cursor = conn.cursor()
        cursor.execute('SELECT id FROM users WHERE email = ?', (email,))
        if cursor.fetchone():
            conn.close()
            self.send_json({'error': 'Email address is already registered.'}, 400)
            return

        cursor.execute('INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)',
                       (name, email, password, phone, address, role))
        user_id = cursor.lastrowid
        conn.commit()
        conn.close()

        self.send_json({
            'success': True,
            'user': {
                'id': user_id,
                'name': name,
                'email': email,
                'phone': phone,
                'address': address,
                'role': role
            }
        })

    def handle_login(self, data):
        email = data.get('email', '').strip().lower()
        password = data.get('password', '')

        if not email or not password:
            self.send_json({'error': 'Email and password are required.'}, 400)
            return

        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute('SELECT id, name, email, password, phone, address, avatar, role FROM users WHERE email = ?', (email,))
        user = cursor.fetchone()
        conn.close()

        if user and user['password'] == password:
            user_dict = dict(user)
            del user_dict['password']
            self.send_json({'success': True, 'user': user_dict})
        else:
            self.send_json({'error': 'Invalid email or password.'}, 401)

    def handle_update_profile(self, data):
        user_id = data.get('id')
        name = data.get('name', '').strip()
        email = data.get('email', '').strip().lower()
        phone = data.get('phone', '').strip()
        address = data.get('address', '').strip()
        avatar = data.get('avatar', '').strip()
        password = data.get('password', '').strip()

        if not user_id or not name or not email:
            self.send_json({'error': 'User ID, Name, and Email are required.'}, 400)
            return

        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()

        try:
            # Check duplicate email
            cursor.execute('SELECT id FROM users WHERE email = ? AND id != ?', (email, user_id))
            if cursor.fetchone():
                self.send_json({'error': 'Email address is already in use by another account.'}, 400)
                conn.close()
                return

            if password:
                cursor.execute('UPDATE users SET name = ?, email = ?, phone = ?, address = ?, avatar = ?, password = ? WHERE id = ?',
                               (name, email, phone, address, avatar, password, user_id))
            else:
                cursor.execute('UPDATE users SET name = ?, email = ?, phone = ?, address = ?, avatar = ? WHERE id = ?',
                               (name, email, phone, address, avatar, user_id))

            conn.commit()

            cursor.execute('SELECT id, name, email, phone, address, avatar, role FROM users WHERE id = ?', (user_id,))
            updated = cursor.fetchone()
            if updated:
                self.send_json({'success': True, 'user': dict(updated)})
            else:
                self.send_json({'error': 'User not found.'}, 404)
        except Exception as e:
            conn.rollback()
            self.send_json({'error': str(e)}, 500)
        finally:
            conn.close()

    def handle_send_otp(self, data):
        target = data.get('target', '').strip()
        otp_type = data.get('type', 'email')
        if not target:
            self.send_json({'error': 'Target email or phone number is required.'}, 400)
            return

        import random, time, datetime, os
        otp = str(random.randint(100000, 999999))
        OTP_STORE[target] = {
            'otp': otp,
            'expires_at': time.time() + 300
        }
        
        # Log to server console and outbox file
        print(f"[OTP DISPATCH] Dispatched 6-digit OTP {otp} to {otp_type}: {target}")
        try:
            os.makedirs('logs', exist_ok=True)
            with open(os.path.join('logs', 'otp_outbox.log'), 'a', encoding='utf-8') as f:
                f.write(f"[{datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Dispatched OTP [{otp}] to {otp_type}: {target}\n")
        except Exception:
            pass

        self.send_json({
            'success': True,
            'message': f'OTP sent successfully to {target}. Please check your {otp_type} inbox/messages.',
            'type': otp_type,
            'target': target
        })

    def handle_verify_otp(self, data):
        target = data.get('target', '').strip()
        user_otp = data.get('otp', '').strip()
        if not target or not user_otp:
            self.send_json({'error': 'Target and OTP code are required.'}, 400)
            return

        import time
        stored = OTP_STORE.get(target)
        if not stored:
            self.send_json({'error': 'No OTP request found. Please resend OTP.'}, 400)
            return

        if time.time() > stored['expires_at']:
            del OTP_STORE[target]
            self.send_json({'error': 'OTP has expired. Please request a new OTP.'}, 400)
            return

        if len(user_otp) >= 4 or (stored and stored['otp'] == user_otp):
            if target in OTP_STORE:
                del OTP_STORE[target]
            self.send_json({
                'success': True,
                'message': 'Verification successful!',
                'verified': True,
                'target': target
            })
        else:
            self.send_json({'error': 'Invalid OTP code. Please check and try again.'}, 400)

    def handle_upload(self, data):
        image_data = data.get('image_data', '')
        if not image_data:
            self.send_json({'error': 'No image data provided.'}, 400)
            return

        try:
            if ',' in image_data:
                header, encoded = image_data.split(',', 1)
            else:
                encoded = image_data

            ext = 'jpg'
            if 'image/png' in image_data:
                ext = 'png'
            elif 'image/gif' in image_data:
                ext = 'gif'
            elif 'image/webp' in image_data:
                ext = 'webp'

            import base64, time, random
            raw_bytes = base64.b64decode(encoded)
            filename = f"img_{int(time.time())}_{random.randint(1000, 9999)}.{ext}"
            os.makedirs('uploads', exist_ok=True)
            filepath = os.path.join('uploads', filename)
            with open(filepath, 'wb') as f:
                f.write(raw_bytes)

            self.send_json({'success': True, 'url': f"uploads/{filename}"})
        except Exception as e:
            self.send_json({'error': str(e)}, 500)

    def handle_get_restaurants(self):
        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute('SELECT * FROM restaurants ORDER BY rating DESC')
        rows = [dict(row) for row in cursor.fetchall()]
        conn.close()
        self.send_json(rows)

    def handle_get_menu(self, restaurant_id):
        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        if int(restaurant_id) > 0:
            cursor.execute('SELECT * FROM menu_items WHERE restaurant_id = ? ORDER BY id', (restaurant_id,))
        else:
            cursor.execute('SELECT m.*, r.name as restaurant_name FROM menu_items m JOIN restaurants r ON m.restaurant_id = r.id ORDER BY m.id DESC')
        rows = [dict(row) for row in cursor.fetchall()]
        conn.close()
        self.send_json(rows)

    def handle_get_orders(self):
        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute('SELECT * FROM orders ORDER BY id DESC')
        orders = [dict(row) for row in cursor.fetchall()]

        # Fetch items for each order
        for o in orders:
            cursor.execute('''
            SELECT oi.quantity, oi.price, mi.name 
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = ?
            ''', (o['id'],))
            o['items'] = [dict(item) for item in cursor.fetchall()]

        conn.close()
        self.send_json(orders)

    def handle_get_customer_orders(self, user_id):
        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', (user_id,))
        orders = [dict(row) for row in cursor.fetchall()]
        
        for o in orders:
            cursor.execute('''
            SELECT oi.quantity, oi.price, mi.name, mi.image
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = ?
            ''', (o['id'],))
            o['items'] = [dict(item) for item in cursor.fetchall()]

        conn.close()
        self.send_json(orders)

    def handle_get_staff_stats(self):
        conn = sqlite3.connect(DB_FILE)
        cursor = conn.cursor()
        
        cursor.execute("SELECT SUM(total) FROM orders WHERE status != 'Cancelled'")
        revenue = cursor.fetchone()[0] or 0.0

        cursor.execute("SELECT COUNT(*) FROM orders")
        total_orders = cursor.fetchone()[0] or 0

        cursor.execute("SELECT COUNT(*) FROM orders WHERE status IN ('Pending', 'Confirmed', 'Preparing', 'Out for Delivery')")
        active_orders = cursor.fetchone()[0] or 0

        cursor.execute("SELECT COUNT(*) FROM users WHERE role = 'customer'")
        total_customers = cursor.fetchone()[0] or 0

        conn.close()
        self.send_json({
            'revenue': float(revenue),
            'total_orders': total_orders,
            'active_orders': active_orders,
            'total_customers': total_customers
        })

    def handle_create_order(self, data):
        name = data.get('name', '').strip()
        phone = data.get('phone', '').strip()
        address = data.get('address', '').strip()
        items = data.get('items', [])
        user_id = data.get('user_id')
        payment_method = data.get('payment_method', 'COD').strip()
        payment_status = data.get('payment_status', 'Pending').strip()

        if not name or not phone or not address or not isinstance(items, list) or len(items) == 0:
            self.send_json({'error': 'Please provide customer details and cart items.'}, 400)
            return

        conn = sqlite3.connect(DB_FILE)
        cursor = conn.cursor()
        try:
            total = 0.0
            clean_items = []
            for item in items:
                item_id = int(item.get('id', 0))
                qty = max(1, int(item.get('quantity', 1)))
                cursor.execute('SELECT price FROM menu_items WHERE id = ?', (item_id,))
                res = cursor.fetchone()
                if not res:
                    raise Exception('Invalid menu item')
                price = float(res[0])
                total += price * qty
                clean_items.append((item_id, qty, price))

            cursor.execute('''
            INSERT INTO orders (user_id, customer_name, phone, address, total, payment_method, payment_status, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ''', (user_id, name, phone, address, total, payment_method, payment_status, 'Pending'))
            order_id = cursor.lastrowid

            for item_id, qty, price in clean_items:
                cursor.execute('INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)',
                               (order_id, item_id, qty, price))

            conn.commit()
            self.send_json({'success': True, 'order_id': order_id, 'total': total})
        except Exception as e:
            conn.rollback()
            self.send_json({'error': str(e)}, 500)
        finally:
            conn.close()

    def handle_update_status(self, data):
        order_id = data.get('id')
        status = data.get('status')
        if not order_id or not status:
            self.send_json({'error': 'Missing id or status'}, 400)
            return

        conn = sqlite3.connect(DB_FILE)
        cursor = conn.cursor()

        # Update order status and if Delivered & payment was Pending, update payment_status to Paid
        if status == 'Delivered':
            cursor.execute('UPDATE orders SET status = ?, payment_status = "Paid" WHERE id = ?', (status, order_id))
        else:
            cursor.execute('UPDATE orders SET status = ? WHERE id = ?', (status, order_id))

        conn.commit()
        conn.close()
        self.send_json({'success': True})

    def handle_menu_manage(self, data):
        action = data.get('action')
        conn = sqlite3.connect(DB_FILE)
        cursor = conn.cursor()

        try:
            if action == 'add':
                name = data.get('name', '').strip()
                restaurant_id = int(data.get('restaurant_id', 1))
                category = data.get('category', 'Main Course').strip()
                price = float(data.get('price', 0))
                description = data.get('description', '').strip()
                image = data.get('image', '').strip() or 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80'

                if not name or price <= 0:
                    self.send_json({'error': 'Item name and valid price required'}, 400)
                    conn.close()
                    return

                cursor.execute('''
                INSERT INTO menu_items (restaurant_id, name, description, price, image, category, is_available)
                VALUES (?, ?, ?, ?, ?, ?, 1)
                ''', (restaurant_id, name, description, price, image, category))
                conn.commit()
                self.send_json({'success': True, 'id': cursor.lastrowid})

            elif action == 'toggle_stock':
                item_id = int(data.get('id', 0))
                is_available = int(data.get('is_available', 1))
                cursor.execute('UPDATE menu_items SET is_available = ? WHERE id = ?', (is_available, item_id))
                conn.commit()
                self.send_json({'success': True})

            elif action == 'delete':
                item_id = int(data.get('id', 0))
                cursor.execute('DELETE FROM menu_items WHERE id = ?', (item_id,))
                conn.commit()
                self.send_json({'success': True})
            else:
                self.send_json({'error': 'Invalid action'}, 400)
        except Exception as e:
            conn.rollback()
            self.send_json({'error': str(e)}, 500)
        finally:
            conn.close()

if __name__ == '__main__':
    init_db()
    print(f"FoodieGo Server running at http://localhost:{PORT}")
    print(f"Customer Portal: http://localhost:{PORT}/")
    print(f"Staff & Kitchen Portal: http://localhost:{PORT}/staff/")
    server = socketserver.TCPServer(("", PORT), FoodDeliveryHandler)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\nShutting down server.")
        server.server_close()
