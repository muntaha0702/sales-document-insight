# Sales Document Insight Web App

A Laravel-based web application that processes sales data from CSV files, provides detailed visualizations, and generates downloadable PDF sales reports. This project demonstrates skills in data handling, database operations, frontend visualization, and PDF generation.

---

## Features

- **CSV Upload & Validation**  
  Upload sales data via CSV with validation for required columns such as transaction ID, date, product name, quantity, unit price, and revenue.

- **Data Import & Storage**  
  Imported sales data is stored securely in a MySQL database with transaction-safe batch inserts.

- **Dynamic Sales Dashboard**  
  - Filter sales data by date range, region, and product categories  
  - Visualize key performance indicators (KPIs) such as total revenue, average revenue, total units sold, return rates, and customer purchase frequency  
  - Chart.js-powered interactive charts for product revenue, category revenue, regional distribution, monthly trends, and more

- **Anomaly Detection & AI Insights**  
  Detects unusual return rates and identifies top products and seasonal sales trends using statistical methods and custom algorithms.

- **PDF Export**  
  Generate comprehensive sales reports in PDF format, including all selected filters and visualized data.

---

## Screenshots

### 1. CSV Upload Page
![WhatsApp Image 2025-08-20 at 01 43 16_6bf8cf5c](https://github.com/user-attachments/assets/8f5e20eb-10b0-4f2e-8f89-4970254ac7ea)


---

### 2. Sales Dashboard with Filters
![WhatsApp Image 2025-08-20 at 01 44 05_c457e47f](https://github.com/user-attachments/assets/4d4cdbf2-8715-4b22-8add-99c552bf3140)


---

### 3. Sales Revenue Visualizations
![WhatsApp Image 2025-08-20 at 01 44 20_10ebe2dc](https://github.com/user-attachments/assets/434cf11c-225c-466a-a6c0-0c2361c9107b)
![WhatsApp Image 2025-08-20 at 01 44 36_08f679d9](https://github.com/user-attachments/assets/13f0f105-7201-496a-91f9-e52230c27bcf)
![WhatsApp Image 2025-08-20 at 01 44 52_85d84d2a](https://github.com/user-attachments/assets/32f0c729-d9fb-4436-9cd0-54d7c8ebbfb0)
![WhatsApp Image 2025-08-20 at 01 45 08_58559183](https://github.com/user-attachments/assets/d8492198-ac35-4c8f-9ff2-b3f4c7eb0c7f)
![WhatsApp Image 2025-08-20 at 01 45 21_6e420320](https://github.com/user-attachments/assets/d085900f-df65-46bc-9ef1-c5c68580d821)







---

## How Revenue is Calculated

- **Total Revenue:** Sum of the `revenue` column for all filtered sales records.
- **Revenue by Product/Category/Region/Store:** Aggregated sums of `revenue` grouped by the respective dimension.
- **Average Revenue:** Average of the `revenue` values over the filtered dataset.
- **Returns:** Calculated as the sum of returned units per product; return rates are computed as `(sum of returns / sum of quantity) * 100`.
- **Monthly Revenue:** Grouped sum of `revenue` by year-month from the `date` field to track trends over time.

These calculations are performed efficiently with Laravel Eloquent query builder, executing optimized SQL queries.

---

## Usage

- Navigate to the **Upload CSV** page to import sales data files.
- After successful import, access the **Sales Dashboard**.
- Use filters to customize the date range, region, and categories.
- Visualize data through interactive charts and insights.
- Click the **Download PDF** button to download a report with current filter settings.

---

## Technologies Used

- Laravel Framework  
- Spatie SimpleExcel (CSV reading)  
- Maatwebsite Excel (optional for import)  
- Chart.js (data visualization)  
- DomPDF (PDF generation)  
- MySQL  (database)  
- Blade Templating Engine

---
