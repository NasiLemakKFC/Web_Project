<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>UTeMHub</title>
  <link rel="stylesheet" href="page4.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
  <header class="navbar">
    <div class="logo">UTeMHub</div>
    <nav>
      <a href="../Page/page3.php">Home Page</a>
      <a href="../Page/page4.php">Search Item</a>
      <a href="../product/store_register.php">Apply as Seller</a>
      <a href="../Page/contact.php">Contact Us</a>
    </nav>
      <div class="profile-cart">
        <a href="../auth/logout.php"><i class="fa-regular fa-user"></i></a>
      </div>

  </header>

  <div class="search-bar">
    <input type="text" placeholder="Search what you need...">
    <i class="fa-solid fa-cart-shopping"></i>
  </div>

  <main>
    <aside>
      <h3><i class="fa-solid fa-filter"></i> Search Filter</h3>
      <div class="filter-section">
        <h4>Rating</h4>
        <div class="stars">
          <span>★★★★★</span><br>
          <span>★★★★☆</span><br>
          <span>★★★☆☆</span><br>
          <span>★★☆☆☆</span><br>
          <span>★☆☆☆☆</span>
        </div>
      </div>
      <div class="filter-section">
        <h4>Type</h4>
        <label><input type="checkbox"> Consumable</label><br>
        <label><input type="checkbox"> Accessories</label><br>
        <label><input type="checkbox"> Clothes</label>
      </div>
    </aside>

    <section class="products-section">
      <div class="sort-bar">
        <span>Sort by</span>
        <button>Relevant</button>
        <button>Latest</button>
        <button>Top Sales</button>
        <select>
          <option>Price</option>
        </select>
        <span class="pagination">Page <button>1</button> <button>2</button> <button>3</button></span>
      </div>

      <div class="products-grid">
        
       
        <div class="product-card">
          <div class="product-img"></div>
          <div class="product-info">
            <h5>Nama Produk</h5>
            <p>RM 0.00</p>
            <div class="stars">★★★★☆</div>
          </div>
        </div>

               <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
        <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
        <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
        <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
        <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
        <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
        <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
        <div class="product-card"><div class="product-img"></div><div class="product-info"><h5>Nama Produk</h5><p>RM 0.00</p><div class="stars">★★★☆☆</div></div></div>
      </div>
    </section>
  </main>
</body>
</html>
