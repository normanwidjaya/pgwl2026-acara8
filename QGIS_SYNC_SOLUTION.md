# Solusi Sinkronisasi QGIS ↔ Web Application

## 📋 Ringkasan Masalah

**Gejala:**
- ✅ Geometri dari Web **BISA** muncul di QGIS  
- ❌ Geometri dari QGIS **TIDAK BISA** muncul di Web

**Penyebab Root Cause:**
Tidak ada mekanisme untuk refresh/polling data dari database setelah QGIS melakukan perubahan. Map di web hanya load data sekali saat halaman pertama kali dimuat.

---

## ✅ Solusi yang Diimplementasikan

### **Fitur: Auto-Refresh / Polling Mechanism**
File yang diubah: [`resources/views/map.blade.php`](resources/views/map.blade.php)

**Apa yang dilakukan:**

1. **Membuat function `refreshAllLayers()`** yang:
   - Membersihkan semua layer yang ada (points, polylines, polygons)
   - Fetch data terbaru dari API endpoints:
     - `GET /api/points` 
     - `GET /api/polylines`
     - `GET /api/polygons`
   - Re-render layers dengan data terbaru

2. **Auto-polling setiap 5 detik** menggunakan `setInterval()`:
   ```javascript
   setInterval(refreshAllLayers, 5000);  // 5000 ms = 5 seconds
   ```

3. **Initial load** saat halaman dibuka dengan memanggil `refreshAllLayers()` sekali

---

## 🔄 Bagaimana Cara Kerjanya

### **Aliran Data (Setelah Fix):**

```
┌─────────────────────────────────────────────┐
│        Browser / Web Map (Leaflet)          │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │  refreshAllLayers() - dipanggil:    │   │
│  │  1. Saat halaman load (initial)     │   │
│  │  2. Setiap 5 detik (polling)        │   │
│  └────────────────┬────────────────────┘   │
└────────────────────┼────────────────────────┘
                     │
                     ↓ (AJAX Query)
         ┌──────────────────────────┐
         │   API Endpoints          │
         │  - GET /api/points       │
         │  - GET /api/polylines    │
         │  - GET /api/polygons     │
         └────────────┬─────────────┘
                      │
                      ↓ (SELECT dengan ST_AsGeoJSON)
         ┌──────────────────────────┐
         │   PostgreSQL + PostGIS   │
         │                          │
         │  Tables:                 │
         │  - points (geom)         │
         │  - polylines (geom)      │
         │  - polygons (geom)       │
         └──────────────────────────┘
              ↑                        
              │ (Direct Write dari QGIS)
         ┌──────────────────────────┐
         │   QGIS Desktop           │
         │  (Direct DB Connection)  │
         └──────────────────────────┘
```

**Flow Timeline:**

```
Time     Web Browser                    Database           QGIS
────────────────────────────────────────────────────────────
 T=0s    Load page → initial refresh    Read geometry    
         ✅ Data tampil di map          ✓

 T=5s    Polling refresh 1              Read geometry    
                                        ✓
 T=5.5s                                                   User edit polygon
                                        ✓ Write updated geom
 T=10s   Polling refresh 2              Read geometry    
         ✅ Polygon update muncul       ✓ (new data)
```

---

## 🧪 Cara Memverifikasi Solusi

### **Testing Steps:**

1. **Di Web Browser:**
   - Buka halaman peta: `http://localhost:8000/peta`
   - Pastikan semua geometri (points, polylines, polygons) tampil

2. **Di QGIS Desktop:**
   - Tambahkan layer dari PostGIS:
     - `Server → PostgreSQL`
     - Host: `127.0.0.1`, Port: `5432`
     - Database: `pgwl_2026`
     - User: `postgres`, Password: `admin`
     - Select tables: `points`, `polylines`, `polygons`

3. **Test Sinkronisasi (QGIS → Web):**
   - Di QGIS: Edit/tambah polygon atau polyline
   - Simpan perubahan ke database
   - Tunggu maksimal 5 detik atau refresh manual
   - ✅ Perubahan akan muncul di web map secara otomatis

4. **Monitor Network Tab (Dev Tools):**
   - Buka Chrome DevTools → Network tab
   - Lihat setiap 5 detik ada request ke:
     - `/api/points`
     - `/api/polylines`  
     - `/api/polygons`
   - Status 200 berarti fetch berhasil

---

## ⚙️ Konfigurasi

### **Mengubah Interval Polling (opsional):**

Edit file [`resources/views/map.blade.php`](resources/views/map.blade.php) di sekitar baris **847**:

**Untuk refresh lebih sering (3 detik):**
```javascript
setInterval(refreshAllLayers, 3000);  // 3 seconds
```

**Untuk refresh lebih jarang (10 detik):**
```javascript
setInterval(refreshAllLayers, 10000);  // 10 seconds
```

### **Performance Notes:**
- **5 detik** = balance yang baik antara responsiveness dan beban server
- **<3 detik** = lebih responsif tapi lebih beban database
- **>10 detik** = lebih hemat resource tapi delay update lebih lama

---

## 🔐 Database Permissions

**Untuk QGIS user, pastikan permission sudah setup:**

```sql
-- Koneksikan sebagai postgres admin
psql -U postgres -d pgwl_2026

-- Grant SELECT pada semua geometri tables
GRANT SELECT ON points, polylines, polygons TO postgres;

-- Grant INSERT/UPDATE/DELETE jika QGIS perlu edit
GRANT SELECT, INSERT, UPDATE, DELETE ON points, polylines, polygons TO postgres;

-- Grant sequence permissions (jika ada auto-increment)
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO postgres;
```

---

## 📊 API Endpoints Reference

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/points` | GET | Semua points sebagai GeoJSON FeatureCollection |
| `/api/point/{id}` | GET | Single point detail |
| `/api/polylines` | GET | Semua polylines sebagai GeoJSON |
| `/api/polygons` | GET | Semua polygons sebagai GeoJSON |

**Response Format:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "Point|LineString|Polygon",
        "coordinates": [lon, lat]
      },
      "properties": {
        "id": 1,
        "name": "Feature name",
        "description": "...",
        "image": "filename.jpg",
        "created_at": "2026-02-26 12:34:56",
        "updated_at": "2026-02-26 12:34:56"
      }
    }
  ]
}
```

---

## 🎯 Solusi Lanjutan (Opsional)

Jika polling setiap 5 detik terasa kurang responsif, ada beberapa alternatif:

### **Option 1: Polling yang lebih agresif**
```javascript
setInterval(refreshAllLayers, 2000);  // 2 seconds
```

### **Option 2: PostgreSQL LISTEN/NOTIFY** (Realtime)
Setup trigger di database yang notify web app via WebSocket saat ada perubahan geometri.

Contoh:
```sql
-- Trigger function
CREATE OR REPLACE FUNCTION notify_geometry_change()
RETURNS TRIGGER AS $$
BEGIN
  PERFORM pg_notify('geometry_change', 
    json_build_object('table', TG_TABLE_NAME, 'operation', TG_OP)::text
  );
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers
CREATE TRIGGER points_notify_change
AFTER INSERT OR UPDATE OR DELETE ON points
FOR EACH ROW EXECUTE FUNCTION notify_geometry_change();
```

### **Option 3: WebSocket Real-time**
Implementasi Pusher atau Laravel WebSocket untuk push notifications langsung tanpa polling.

---

## 📝 File yang Dimodifikasi

- ✅ **[resources/views/map.blade.php](resources/views/map.blade.php)**
  - Baris ~770: Fungsi `refreshAllLayers()` ditambahkan
  - Baris ~838: `Initial load dengan refreshAllLayers()`
  - Baris ~841: `setInterval polling setiap 5 detik`

---

## 🚀 Testing Checklist

- [ ] Web browser menampilkan semua geometri dari database
- [ ] Setiap 5 detik ada network request ke `/api/points`, `/api/polylines`, `/api/polygons`
- [ ] Edit geometri di QGIS → simpan ke database
- [ ] Refresh terjadi otomatis di web map dalam 5 detik
- [ ] Performance tidak lag/tidak berat
- [ ] Console browser (DevTools) tidak ada error

---

## 📞 Troubleshooting

### **Problem: Geometri QGIS masih tidak muncul di web**

**Kemungkinan:**
1. QGIS belum save data (pastikan save/sync)
2. QGIS write permission tidak ada ke database
   - **Solusi:** Run `GRANT SELECT, INSERT, UPDATE, DELETE ON points, polylines, polygons TO postgres;`
3. API endpoint error
   - **Check:** Network tab → `/api/points` → Response code tidak 200

### **Problem: Map lag/performance issue**

**Solusi:**
1. Naikkan interval polling dari 5s menjadi 10s
2. Filter geometri hanya yang visible di viewport (tidak implement yet)
3. Optimize database query dengan index pada geom column

### **Problem: Perubahan QGIS muncul dengan delay > 5 detik**

**Kemungkinan:**
1. Database/server response slow
   - Check: `EXPLAIN ANALYZE SELECT ST_AsGeoJSON(geom) FROM points;`
2. Network latency
3. Browser cache
   - **Solution:** Buka Network tab → Disable cache

---

## 📚 Referensi

- **Leaflet GeoJSON Layer:** https://leafletjs.com/examples/geojson/
- **PostGIS ST_AsGeoJSON:** https://postgis.net/docs/ST_AsGeoJSON.html
- **QGIS & PostgreSQL:** https://docs.qgis.org/3.28/en/docs/user_manual/managing_data_source/opening_data.html
- **GeoJSON Spec:** https://tools.ietf.org/html/rfc7946
