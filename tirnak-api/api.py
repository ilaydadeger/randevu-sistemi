from fastapi import FastAPI, UploadFile, File
from ultralytics import YOLO
import cv2
import numpy as np

# FastAPI uygulamamızı başlatıyoruz
app = FastAPI(title="Tırnak Analiz API")

# Eğittiğimiz modeli yüklüyoruz
model = YOLO("best.pt")

# Ücret alınmayacak ve sadece sayımı yapılacak temel tırnak formları
temel_tirnaklar = [
    "normal_badem", "normal_stiletto", "normal_balerin", 
    "kisa_badem", "normal_kare", "uzun_badem"
]

@app.post("/analiz")
async def tirnak_analiz_et(file: UploadFile = File(...)):
    # 1. PHP'den gelen fotoğrafı okuyoruz
    image_bytes = await file.read()
    image_array = np.frombuffer(image_bytes, np.uint8)
    img = cv2.imdecode(image_array, cv2.IMREAD_COLOR)

    # 2. Yapay zekadan tahmin alıyoruz
    results = model.predict(source=img, conf=0.25)
    
    # 3. Sonuçları ayrıştırıyoruz
    bulunan_sayilar = {
        "toplam_tirnak": 0,
        "detaylar": {},
        "temel_detaylar": []
    }

    for box in results[0].boxes:
        class_id = int(box.cls[0])
        class_name = model.names[class_id]

        # Her tespit edilen nesne toplam tırnak sayısına katkıda bulunur
        bulunan_sayilar["toplam_tirnak"] += 1

        # Eğer bulduğu etiket temel tırnaklardan biriyse sadece temel listeye ekle
        if class_name in temel_tirnaklar: 
            bulunan_sayilar["temel_detaylar"].append(class_name)
        # Değilse (taş, çizim, ombre vb.) ekstra ücretli detaylara ekle
        else:
            if class_name in bulunan_sayilar["detaylar"]:
                bulunan_sayilar["detaylar"][class_name] += 1
            else:
                bulunan_sayilar["detaylar"][class_name] = 1

    # 4. JSON olarak PHP'ye yanıt dönüyoruz
    return bulunan_sayilar