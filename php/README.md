# ระบบจัดตารางเวรพนักงาน 2026

System แปลงจาก Google Apps Script เป็น PHP พร้อม Docker สำหรับการจัดตารางเวรพนักงาน 2 กลุ่ม (A และ B) ใน 2 สถานที่ (TP และ RS)

## 🎯 Features

- ✅ จัดเวร TP (Office 1): 1 คนจากกลุ่ม A + 1 คนจากกลุ่ม B
- ✅ จัดเวร RS (Office 2): 1 คน (สัดส่วน B:A = 80:20)
- ✅ เงื่อนไขการจัดเวรครบถ้วนตาม App Script
  - กลุ่ม A: 2-3 ครั้ง/เดือน
  - กลุ่ม B: ขั้นต่ำ 1 ครั้ง/เดือน (บังคับ)
  - กระจายวันในสัปดาห์ให้สมดุล
  - ไม่ทำงานติดกัน (มี fallback)
- ✅ รองรับวันหยุดราชการไทย ปี 2026
- ✅ กฎพิเศษสำหรับเดือนต่างๆ (ม.ค. ก.พ. ก.ค. ส.ค. = เต็มสัปดาห์)

## 🖥️ Web Interface

1. **ตารางเวรทั้งปี** - แสดงตารางเวรทั้งปีแบบตาราง
2. **ปฏิทินรายเดือน** - ปฏิทิน 12 เดือนแบบ grid
3. **สรุปสถิติ** - จำนวนเวร TP/RS ของแต่ละคน
4. **กระจายวันในสัปดาห์** - แสดงการกระจายเวรในแต่ละวัน
5. **สรุป TP รายเดือน** - ตรวจสอบการกระจายรายเดือน
6. **รายละเอียดพนักงาน** - ค้นหาเวรของแต่ละคน
7. **วันหยุด** - รายการวันหยุดราชการ
8. **เงื่อนไขการจัดเวร** - อธิบายกฎทั้งหมด

## 🚀 การติดตั้งและรัน

### Prerequisites
- Docker
- Docker Compose

### วิธีรัน

```bash
cd /Users/pwachira/code/counter_service/php
docker-compose up -d
```

เปิดเบราว์เซอร์ที่: `http://localhost:8080`

### หยุดการทำงาน

```bash
docker-compose down
```

## 📁 โครงสร้างโปรเจค

```
php/
├── Dockerfile              # Docker configuration
├── docker-compose.yml      # Docker Compose setup
├── apache-config.conf      # Apache configuration
├── src/
│   ├── index.php          # Main entry point
│   ├── config/            # Configuration files
│   │   ├── constants.php  # Constants (timezone, year, etc.)
│   │   └── holidays.php   # Thai holidays 2026
│   ├── models/            # Data models
│   │   ├── Staff.php      # Staff management
│   │   └── Schedule.php   # Schedule data structure
│   ├── scheduling/        # Scheduling algorithms
│   │   ├── Scheduler.php  # Main orchestrator
│   │   ├── TpScheduler.php # TP scheduling logic
│   │   └── RsScheduler.php # RS scheduling logic
│   ├── utils/             # Utilities
│   │   └── DateHelper.php # Date functions
│   ├── views/             # Web views
│   │   ├── schedule.php   # Main schedule table
│   │   ├── calendar.php   # Monthly calendar
│   │   ├── summary.php    # Summary statistics
│   │   ├── dow_distribution.php # DoW distribution
│   │   ├── monthly_tp.php # Monthly TP summary
│   │   ├── staff_details.php # Staff lookup
│   │   ├── holidays.php   # Holidays list
│   │   └── conditions.php # Rules documentation
│   └── data/
│       └── names.json     # Staff names (EDIT THIS)
└── public/
    ├── css/
    │   └── style.css      # Styling
    └── js/
        └── app.js         # Client-side JS
```

## ⚙️ การกำหนดค่า

### แก้ไขรายชื่อพนักงาน

แก้ไขไฟล์ `src/data/names.json`:

```json
{
  "groupA": [
    {"code": "A1", "name": "ชื่อจริง"},
    {"code": "A2", "name": "ชื่อจริง"}
  ],
  "groupB": [
    {"code": "B1", "name": "ชื่อจริง"},
    {"code": "B2", "name": "ชื่อจริง"}
  ]
}
```

### ล้างข้อมูล Cache

ถ้าแก้ไขรายชื่อหรือต้องการสร้างตารางใหม่:

1. ลบไฟล์ session ใน container
2. หรือรีสตาร์ท Docker: `docker-compose restart`

## 🎨 Design

- Modern gradient UI
- Responsive design (mobile-friendly)
- Thai language support
- Color-coded indicators:
  - 🟢 Green: Good balance (DoW diff ≤ 2)
  - 🟡 Yellow: Medium (Monthly 2-3 for Group A)
  - 🔴 Red: Needs attention
- Print-friendly

## 🔧 Technical Details

### Scheduling Algorithm

- **Deterministic**: เหมือน App Script ทุกครั้ง
- **Stable Selection**: เลือกคนแรกเมื่อคะแนนเท่ากัน
- **Natural Sort**: เรียงรหัสตามตัวเลข
- **Progressive Fallback**: ผ่อนปรนข้อจำกัดทีละขั้น (RS)

### Performance

- Session caching (คำนวณครั้งเดียวต่อ session)
- Efficient array operations
- Minimal database queries (ไม่ใช้ DB)

## 📊 Validation

ระบบนี้ผ่านการตรวจสอบ:
- ✅ วันทำการถูกต้องตามกฎ
- ✅ กลุ่ม B ทุกคนได้เวรอย่างน้อย 1 ครั้ง/เดือน
- ✅ กลุ่ม A ส่วนใหญ่ได้ 2-3 ครั้ง/เดือน
- ✅ สัดส่วน B:A ประมาณ 80:20
- ✅ ไม่มีคนทำงานติดกัน (ยกเว้น fallback)
- ✅ การกระจายวันในสัปดาห์สมดุล

## 📝 License

สร้างสำหรับองค์กร - ใช้ภายในเท่านั้น

## 🙏 Credits

แปลงจาก Google Apps Script โดยรักษาตรรกะการจัดเวรเดิมไว้ทุกอย่าง
