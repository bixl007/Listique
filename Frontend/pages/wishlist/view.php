<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wishlist</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://rsms.me/">
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
  <style>
    :root { font-family: 'Inter', sans-serif; }
    body {
      background: #000;
      min-height: 100vh;
      margin: 0;
      color: #e5e7eb;
    }
    .gradient-title {
      background: linear-gradient(90deg, #6366f1 0%, #a855f7 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-fill-color: transparent;
      display: inline-block;
    }
  </style>
</head>
<body class="flex flex-col items-center min-h-screen bg-black">
  <div id="wishlist-owner-title" class="gradient-title text-3xl font-extrabold mt-12 mb-6 text-center"></div>
  <div class="w-full max-w-lg mx-auto p-8 bg-gradient-to-br from-slate-900/80 via-slate-800/80 to-indigo-900/80 rounded-2xl shadow-2xl border border-slate-800">
    <h2 class="text-2xl font-bold gradient-title mb-4 text-center flex justify-center items-center" id="wishlist-title">Loading...</h2>
    <div class="text-center text-slate-400 text-sm mb-4" id="wishlist-meta"></div>
    <ul class="space-y-4" id="wishlist-items"></ul>
    <div class="text-slate-400 text-center mt-4" id="wishlist-desc"></div>
  </div>
  <script>
    function getWishlistId() {
      const params = new URLSearchParams(window.location.search);
      return params.get('id');
    }

    async function fetchWishlist(id) {
      const res = await fetch(`../../../Backend/wishlist/get_single.php?id=${encodeURIComponent(id)}`, {
        credentials: 'include',
        cache: 'no-cache'
      });
      if (!res.ok) throw new Error('Wishlist not found');
      return await res.json();
    }

    function renderWishlist(wishlist) {
      document.title = wishlist.main_title;
      document.getElementById('wishlist-owner-title').innerHTML = `<span>${wishlist.main_title}</span>`;
      document.getElementById('wishlist-title').textContent = wishlist.title;
      document.getElementById('wishlist-meta').textContent =
        `Created on ${new Date(wishlist.created_at).toLocaleDateString()}`;
      const itemsList = document.getElementById('wishlist-items');
      itemsList.innerHTML = '';
      if (wishlist.items && Array.isArray(wishlist.items) && wishlist.items.length > 0) {
        wishlist.items.forEach(item => {
          const li = document.createElement('li');
          li.className = 'bg-slate-800/80 rounded-xl px-4 py-3 text-slate-100 border border-slate-700 flex items-center gap-2';
          li.innerHTML = `<span class="text-purple-400 text-lg font-bold">•</span> <span>${item}</span>`;
          itemsList.appendChild(li);
        });
      } else if (wishlist.description) {
        const li = document.createElement('li');
        li.className = 'bg-slate-800/80 rounded-xl px-4 py-3 text-slate-100 border border-slate-700 flex items-center gap-2';
        li.innerHTML = `<span class="text-purple-400 text-lg font-bold">•</span> <span>${wishlist.description}</span>`;
        itemsList.appendChild(li);
      }
      if (wishlist.description && (!wishlist.items || wishlist.items.length === 0)) {
        document.getElementById('wishlist-desc').textContent = wishlist.description;
      } else {
        document.getElementById('wishlist-desc').textContent = '';
      }
    }

    async function main() {
      const id = getWishlistId();
      if (!id) {
        document.getElementById('wishlist-owner-title').textContent = 'Invalid wishlist link';
        document.getElementById('wishlist-title').textContent = '';
        return;
      }
      try {
        const wishlist = await fetchWishlist(id);
        if (!wishlist.items && wishlist.description) {
          try {
            const parsed = JSON.parse(wishlist.description);
            if (Array.isArray(parsed)) wishlist.items = parsed;
          } catch {}
        }
        renderWishlist(wishlist);
      } catch (e) {
        document.getElementById('wishlist-owner-title').textContent = 'Wishlist not found';
        document.getElementById('wishlist-title').textContent = '';
        document.getElementById('wishlist-meta').textContent = '';
        document.getElementById('wishlist-items').innerHTML = '';
        document.getElementById('wishlist-desc').textContent = '';
      }
    }

    main();
  </script>
</body>
</html>
