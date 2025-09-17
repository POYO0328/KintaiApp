# 勤怠アプリ

## 環境構築

- Docker ビルド
  1.git clone git@github.com:POYO0328/KintaiApp.git
  2.docker-compose up -d --build

\*MySQL は、OS によって起動しない場合があるのでそれぞれの PC に合わせて docker-compose.yml ファイルを編集してください。

## Laravel 環境構築

1. docker-compose exec php bash
2. composer install
3. .env.example をコピーして .env を作成し、環境変数を修正してください。
（DB 接続情報と Mailtrap 設定を環境に合わせて修正してください）
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed

## 使用技術

・PHP 8.4.3
・Laravel 8.83.8
・MySQL 8.0

## メール認証
mailtrapというツールを使用しています。
以下のリンクから会員登録をしてください。　
https://mailtrap.io/

メールボックスのIntegrationsから 「laravel 7.x and 8.x」を選択し、　
.envファイルのMAIL_MAILERからMAIL_ENCRYPTIONまでの項目をコピー＆ペーストしてください。　
MAIL_FROM_ADDRESSは任意のメールアドレスを入力してください。　

## URL

・開発環境：http://localhost/
・phpMyAdmin：http://localhost:8080/

## 仕様

1. 同一日付はユーザーによる申請は一回までの仕様です。
2. ユーザーの承認済みの画面には、最終的な勤怠時間が表示されます。承認後、管理者によって変更された場合、その時間が表示されます。

## テスト用管理者/データ
1. 'name' => '山田 太郎',
   'email' => 'yamada@example.com'

## テストユーザー/データ
1. 'name' => '佐藤 花子',
   'email' => 'sato@example.com'

2. 'name' => '鈴木 次郎',
   'email' => 'suzuki@example.com'

3. 'name' => '田中 美咲',
   'email' => 'tanaka@example.com'

4. 'name' => '高橋 健太',
   'email' => 'takahashi@example.com'

全員のパスワード：password
直近１ヵ月分のダミーデータを作成しております。

## テストコード
1. 認証機能(一般ユーザー)　php artisan test --filter=RegisterTest
2. ログイン機能(一般ユーザー)　※対象外
3. ログイン機能(管理者)　※対象外
4. 日時取得機能　※Javascriptにて日時取得しており、テスト作成不可　
5. ステータス確認機能　php artisan test --filter=
6. 出勤機能　php artisan test --filter=ClockInTest
7. 休憩機能　php artisan test --filter=BreakTest
8. 退勤機能　php artisan test --filter=ClockOutTest
9. 勤怠一覧情報取得機能(一般ユーザー)　※対象外
10. 勤怠詳細情報取得機能(一般ユーザー)　※対象外
11. 勤怠詳細情報修正機能(一般ユーザー)　※対象外
12. 勤怠一覧情報取得機能(管理者)　php artisan test --filter=AttendanceListTest
13. 勤怠詳細情報取得・修正機能(管理者)　php artisan test --filter=AttendanceDetailTest
14. ユーザー情報取得機能(管理者)　php artisan test --filter=AdminUserListTest
15. 勤怠情報修正機能(管理者)　php artisan test --filter=PendingListTest
16. メール認証機能　※対象外

※テスト実行の前に、web.php内の
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
のコメントアウトを有効にし、実行をお願いいたします。
