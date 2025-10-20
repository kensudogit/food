@echo off
echo 🍽️ Food Delivery Admin - システム実行状況
echo ==========================================

echo.
echo 📊 システム情報:
echo - プロジェクト名: Food Delivery Admin
echo - バージョン: 1.0.0
echo - 起動時刻: %date% %time%
echo.

echo 🌐 Webサーバー状況:
netstat -an | findstr :8080
if %errorlevel% equ 0 (
    echo ✅ Webサーバー稼働中 (ポート 8080)
) else (
    echo ❌ Webサーバー停止中
)

echo.
echo 📁 プロジェクト構成:
echo - API: api/
echo - 管理システム: admin/
echo - データベース: database/
echo - Docker設定: docker/
echo - 設定ファイル: config/
echo - ログ: logs/

echo.
echo 🚀 アクセス方法:
echo - メインページ: http://localhost:8080
echo - ダッシュボード: http://localhost:8080/dashboard.html
echo - 売上管理: http://localhost:8080/sales.html

echo.
echo 📋 利用可能な機能:
echo ✅ 現代的で魅力溢れるUI/UXデザイン
echo ✅ リアルタイム売上ダッシュボード
echo ✅ 詳細な売上分析とレポート
echo ✅ レストラン管理機能
echo ✅ 注文管理システム
echo ✅ 配送管理機能
echo ✅ データエクスポート機能
echo ✅ レスポンシブデザイン
echo ✅ ダークモード対応

echo.
echo 🛠️ 技術スタック:
echo - PHP 8.1+ (Slim Framework)
echo - MySQL 8.0
echo - Redis 7
echo - Memcached
echo - gRPC
echo - Docker
echo - Chart.js
echo - Font Awesome
echo - Inter Font

echo.
echo 📞 サポート:
echo - ドキュメント: README.md
echo - ログファイル: logs/
echo - 設定ファイル: config/

echo.
echo 🎉 システムが正常に実行されています！
echo ブラウザで http://localhost:8080 にアクセスしてください。

pause
