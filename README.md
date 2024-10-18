# bbs-websystem

目的

利用者のブラウザから、質問やアイデアの発信、質問等に対する意見の発信や閲覧などを行う。


要件定義

✅ とことんシンプルな質問サイトとする。
✅ 利用者は、質問や回答を見ることが出来る。
✅ 回答は、質問に紐つく。
✅ １質問に対する回答は、複数存在する。
✅ 利用者は、質問や回答を投稿することが出来る。
✅ 質問や回答は、誰でも見ることが出来る。
✅ 質問や回答を投稿する場合は、ユーザー認証が必要。
✅ ログインは、ID とパスワードで行う。
✅ ログインするには、ユーザー登録が必要。
✅ ログイン後は、ログアウトすることが出来る。
✅ 質問や回答には、画像は不要。
✅ テキストのみ投稿できる。
✅ 質問や回答は、投稿したら、編集できなくてよい
✅ 但し、削除はできる。
✅ 回答の削除は、指定した回答のみを削除する。
✅ 質問の削除は、回答と共に削除する。
✅ 削除したものは、データベースには、残しておく。
✅ 投稿した利用者のみ削除することが出来る。
✅ 質問の削除は、質問を投稿した利用者のみ可能で、他者の回答も併せて削除する
✅ 回答の削除は、投稿した自身の回答のみ
✅ 質問に、カテゴリのような区分は不要
✅ ユーザーの退会やパスワード再発行は不要
✅ 自動ログイン機能（１度ログインに成功していれば、１週間程度ログイン認証操作を省略する）
✅ いいねボタンの作成
✅ 質問に対する回答数の表示：各質問に対して、いくつの回答があるかを表示します。
✅ 質問クローズ機能：質問を解決済みとしてマークし、ユーザーが新しい回答を投稿できないようにします。
✅ パスワード変更機能：ユーザーが自身のパスワードを変更できるようにします。
✅ 匿名での質問・回答機能：ユーザーが名前を表示せずに質問や回答を投稿できるようにします。
✅ 画像アップロード機能：質問や回答に画像を添付できるようにします。
✅ 質問検索機能
✅ログイン中は、TOPページで「ログインしています」とメッセージを表示、ログイン項目が表示されない。

未実装追加要件（今後追加したい）
⚫ 長い文章の短縮表示機能：長い質問や回答を短縮表示し、ユーザーが「続きを読む」できるようにします。
⚫ 質問数・回答数が多い場合のページング機能：質問や回答が多い場合に、ページを分けて表示します。
⚫ 質問のカテゴリ化：質問をカテゴリに分けて整理します。
⚫ ユーザープロフィール機能：ユーザーの詳細情報や投稿履歴を表示する機能
⚫ タグ機能：質問にタグを付けて分類し、タグによる検索を可能にする機能
⚫ コメント機能：質問や回答にコメントを追加できるようにする機能
⚫ 通知機能：自分の質問に回答があった場合や、コメントがついた場合に通知する機能
⚫ ソート機能：質問一覧を新着順、人気順（いいね数）などでソートできるようにする機能
⚫ ページネーション：質問一覧や回答一覧を複数ページに分割して表示する機能
⚫ マークダウン対応：質問や回答の本文でマークダウン記法を使用できるようにする機能
⚫ 管理者機能：不適切な投稿の管理や、ユーザー管理を行える管理者ページの作成
