# 📈 InversIOL - Automated Trading Bot & Portfolio Manager

InversIOL is a fully automated algorithmic trading system and portfolio management dashboard tailored for the Argentine stock market (BCBA). Built with **Laravel** and **Filament PHP**, it connects directly to the InvertirOnline (IOL) API to execute real-time market orders based on customizable technical thresholds.

## 🚀 Key Features

* **Automated Trading Engine (Cron-based):** * Background jobs monitor the market via the IOL API.
  * Evaluates current holdings and executes **Take Profit (TP)** or **Stop Loss (SL)** market orders autonomously based on user-defined parameters per asset.
* **Opportunity Hunter (Limit-Buy Simulator):** * Users can configure target prices for specific CEDEARs or local stocks (e.g., SPY, NVDA, TSLA).
  * The bot silently monitors quotes and executes purchase orders only when the target price is hit and liquid funds are available.
* **Smart Alerts & Opportunity Cost:** * Calculates asset stagnation times. If capital is immobilized without significant yield, the system dispatches email alerts to suggest portfolio rotation.
* **Filament Admin Dashboard:** * Real-time UI to manage parameters, view historical yields, and track liquid assets.
  * Includes a "Panic Button" for manual, immediate market-order liquidation of assets directly from the UI.
* **Automated Data Sync:** * Periodically pulls immutable transaction history and reconciles actual holdings directly from the broker.

## 🛠️ Tech Stack

* **Backend:** PHP 8.x, Laravel 11.x
* **Frontend:** Filament PHP v3 (TALL Stack)
* **Database:** MySQL / MariaDB
* **Task Scheduling:** Laravel Task Scheduling (Cron)
* **Integrations:** InvertirOnline (IOL) REST API

## 🏗️ Architecture & Commands

The core logic resides in dedicated Artisan commands scheduled to run during market hours (11:00 to 17:00 BCBA):

* `php artisan broker:sync` - Reconciles remote portfolio and local DB.
* `php artisan broker:trade` - Evaluates active holdings against TP/SL thresholds.
* `php artisan broker:cazar` - Hunts for predefined price drops and executes buy orders.

## ⚙️ Setup & Installation

1. Clone the repository.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and configure your database and IOL API credentials.
4. Run `php artisan key:generate`.
5. Run migrations: `php artisan migrate`.
6. Set up the Cron job on your server to run `php artisan schedule:run` every minute.

> **Note:** You must have an active InvertirOnline account with API access enabled.

## ⚠️ Disclaimer

This software is for educational and portfolio demonstration purposes. Algorithmic trading involves significant financial risk. The author is not responsible for any financial losses incurred through the use of this automated system.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).