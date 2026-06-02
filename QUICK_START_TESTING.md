# 🚀 Quick Start: Testing QGIS ↔ Web Sync

## ⏱️ Waktu Setup: 2 menit

### Step 1: Pastikan Laravel Running
```bash
php artisan serve
# atau jika sudah ada: http://localhost:8000
```

### Step 2: Setup QGIS Connection (1st time only)

**Di QGIS Desktop:**

1. **Browser Panel** → klik **"New PostgreSQL Connection"**
   
2. **Fill form:**
   ```
   Name: pgwl_2026
   Host: 127.0.0.1
   Port: 5432
   Database: pgwl_2026
   Username: postgres
   Password: admin
   ✓ Save password
   ✓ Allow saving credentials
   ```

3. **Click "Test Connection"** → Should success ✅

4. **Click "OK"**

5. **Double-click connection** → Expand dan select:
   - `public → points`
   - `public → polylines`
   - `public → polygons`
   
6. **Right-click → Add Layer to Project**

---

### Step 3: Test Sinkronisasi

#### **Test A: Web → QGIS (Sudah bekerja, confirmasi saja)**

1. Buka browser: `http://localhost:8000/peta`
2. Login jika belum
3. Di Web: **Draw** sebuah point baru → Save
4. Di QGIS: Refresh (F5) atau right-click layer → Refresh
5. ✅ Point baru muncul di QGIS

#### **Test B: QGIS → Web (Yang diperbaiki)**

1. Di QGIS: Edit/Tambah polygon baru atau ubah existing polyline
2. **Save layer to Database** (QGIS akan auto-commit)
3. Buka tab browser (Web map): `http://localhost:8000/peta`
4. **Tunggu max 5 detik** atau lihat Network tab untuk lihat polling
5. ✅ Perubahan QGIS akan muncul di map otomatis!

---

## 🔍 Verification Checklist

### A. Visual Verification
```
☐ Web map menampilkan semua existing geometri
☐ Buka QGIS, edit satu feature (misalnya ubah polyline)
☐ Tunggu 5 detik
☐ Perubahan tampil di web map otomatis
```

### B. Network Verification (DevTools)
```
1. Buka Web map: http://localhost:8000/peta
2. Buka DevTools: F12 → Network tab
3. Filter: /api/
4. Amati:
   ☐ Request /api/points terlihat setiap 5 detik
   ☐ Request /api/polylines terlihat setiap 5 detik
   ☐ Request /api/polygons terlihat setiap 5 detik
   ☐ Status semua: 200 OK
```

### C. Browser Console Verification
```
1. DevTools → Console tab
2. Tidak boleh ada error merah
3. Seharusnya clean atau hanya warning
```

---

## 📊 Expected Network Pattern

**Setiap 5 detik, akan ada 3 request:**

```
GET /api/points         → 200 OK (50 - 200ms)
GET /api/polylines      → 200 OK (50 - 200ms)
GET /api/polygons       → 200 OK (50 - 200ms)
```

**Response size:** ~1-10 KB tergantung banyaknya geometri

**Timing Pattern:**
```
T=0s    Initial load (halaman dibuka)
T=5s    Polling 1
T=10s   Polling 2
T=15s   Polling 3
... dan seterusnya
```

---

## 🛠️ Troubleshooting Quick Check

### ❌ Geometri tidak muncul di web
**Fix:**
```bash
# 1. Check jika API endpoint error
curl http://localhost:8000/api/points
# Should return JSON, not error

# 2. Check database connection
php artisan tinker
# Ctrl+D exit

# 3. Reload halaman map
# http://localhost:8000/peta
```

### ❌ Perubahan QGIS tidak muncul di web
**Fix:**
```bash
# 1. Pastikan QGIS save data (File → Save atau Ctrl+S)
# 2. Check DevTools Console:
#    - F12 → Console
#    - Pastikan tidak ada error merah
# 3. Check Network tab polling aktif (lihat setiap 5 detik)
# 4. Tunggu max 5 detik
# 5. Manual refresh: F5
```

### ❌ API response error (500)
**Fix:**
```bash
# Check server log
php artisan serve
# Lihat error message di terminal output

# atau check storage log
tail -f storage/logs/laravel.log
```

### ❌ Database connection error
**Fix:**
```bash
# Test PostgreSQL connection
psql -U postgres -d pgwl_2026 -c "SELECT count(*) FROM points;"

# Should return number without error
```

---

## 📈 Performance Monitoring

### Monitor setiap polling:
```javascript
// Paste di Console (F12)
console.log('Polling active - every 5 seconds');
// Buka Network tab untuk melihat request

// Untuk melihat actual timing:
performance.mark('sync-start');
// Will be marked automatically
```

### Cek database query speed:
```sql
-- Connect ke PostgreSQL
psql -U postgres -d pgwl_2026

-- Check query time
EXPLAIN ANALYZE SELECT ST_AsGeoJSON(geom) FROM points;

-- If too slow, dapat optimize dengan index:
CREATE INDEX idx_points_geom ON points USING GIST(geom);
```

---

## 🎯 Expected Results

**Sebelum fix:**
- ❌ QGIS → Web = Manual refresh perlu atau tidak sync

**Sesudah fix:**
- ✅ QGIS → Web = Auto sync setiap 5 detik
- ✅ Web → QGIS = Tetap bekerja seperti sebelumnya
- ✅ Network traffic = ~3 request per 5 detik (minimal)
- ✅ Server load = Negligible (simple SELECT queries)

---

## 🧪 Advanced Testing (Opsional)

### Test 1: Bulk data update
```
# Di QGIS: Edit 10 geometri sekaligus
# Result: Web map meng-update semua dalam 5 detik
```

### Test 2: Concurrent access
```
# User A di QGIS: Edit polygon
# User B di Web: Lihat update otomatis
# Result: Sinkronisasi real-time terjadi
```

### Test 3: Long running session
```
# Biarkan map terbuka 1 jam
# Cek Network tab: polling terus-menerus setiap 5 detik
# Result: No memory leak, stable performance
```

---

## 📝 Catatan Penting

1. **Polling interval = 5 detik**
   - Bisa diubah di `resources/views/map.blade.php` line ~841
   - Untuk lebih frequent: ubah 5000 → 3000 (3 detik)
   - Untuk lebih jarang: ubah 5000 → 10000 (10 detik)

2. **Database permission**
   - QGIS user perlu SELECT, INSERT, UPDATE, DELETE permission
   - Sudah di-setup jika user = postgres

3. **Browser cache**
   - Jika data tidak update, buka DevTools → Settings → Disable cache
   - atau hardrefresh: Ctrl+Shift+R

4. **Multiple users**
   - Jika ada multiple browser tabs/users: mereka semua akan auto-sync
   - Tidak perlu communicate antar user

---

## 📞 Still Not Working?

**Checklist:**

1. ☐ Laravel server running (`php artisan serve`)
2. ☐ PostgreSQL running (port 5432)
3. ☐ Database connected (`psql -U postgres -d pgwl_2026`)
4. ☐ QGIS connected to PostgreSQL (test connection successful)
5. ☐ Web map opened: `http://localhost:8000/peta` (logged in)
6. ☐ Browser DevTools: No console errors
7. ☐ Network tab: Seeing `/api/points` requests every 5 seconds
8. ☐ QGIS: Data saved to database (Ctrl+S or File → Save)

**If all checked and still not working:**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check if API working
curl -s http://localhost:8000/api/points | head

# Check DB directly
psql -U postgres -d pgwl_2026 -c "SELECT id, name FROM points LIMIT 5;"

# Restart Laravel
# Kill: Ctrl+C
# Start: php artisan serve
```

---

## 🎉 Success Signs

✅ **Jika Anda melihat:**
- Geometri dari QGIS muncul di web dalam 5 detik
- Network tab menunjukkan polling requests `/api/points` setiap 5 detik
- Tidak ada error di browser console
- Map responsif dan smooth

**Maka solusi sudah BERHASIL implementasi!** 🎊
