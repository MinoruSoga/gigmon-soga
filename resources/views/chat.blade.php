@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chat</h1>
    <script>
      function callgpt() {
        _ajax('/api/callgpt', 'POST', {
          'message': 'ビジネスで必要なマインドを教えてください。'
        });
      }
      function callgpt3() {
        _ajax('/api/callgpt', 'POST', {
          'message': 'ビジネスで必要なマインドを教えてください。',
          'model': 'gpt-3.5-turbo'
        });
      }
      function categories() {
        _ajax('/api/categories', 'GET');
      }
      function prompts() {
        _ajax('/api/prompts/general', 'GET');
      }
      function prompts1() {
        _ajax('/api/prompts/general/1', 'GET');
      }
      function prompts2() {
        _ajax('/api/prompts/individual', 'GET');
      }
      function calldocsbot1() {
        _ajax('/api/calldocs', 'POST', {
          'message': '勤怠について',
          'conversationToken': 'test-chat-2023-06-06'
        });
      }
      function calldocsbot2() {
        _ajax('/api/calldocs', 'POST', {
          'message': '2',
          'conversationToken': 'test-chat-2023-06-06'
        });
      }
      function history1() {
        _ajax('/api/history/1', 'GET');
      }
      function history2() {
        _ajax('/api/history/2', 'GET');
      }
      function error() {
        // force-error 画面に遷移する
        location.href = '/force-error';
      }
      function function_calling() {
        _ajax('/api/callgpt', 'POST', {
          //'message': '次のWebサイトの翻訳と要約をしてください。\nhttps://cointelegraph.com/news/sec-and-binance-us-strike-deal-on-asset-access',
          'message': '次のWebサイトの翻訳と要約をしてください。\nhttps://www3.nhk.or.jp/nhkworld/en/news/20230620_29/',
          'functionId': 1
        });
      }
      function speed() {
        _ajax('/api/speed', 'GET');
      }
      function check1() {
        _ajax('/api/checkMessage', 'POST', {
          'message': 'これにはNGワードが含まれています。'
        });
      }
      function check2() {
        _ajax('/api/checkMessage', 'POST', {
          'message': '私の名前は山田太郎です。'
        });
      }
      function hide() {
        _ajax('/api/hideHistory', 'POST', {
          'conversationToken': '92516249-51a7-46d1-b19b-3c7e8acdffea',
          'mode': 1
        });
      }
      function show() {
        _ajax('/api/showHistory', 'POST', {
          'conversationToken': '92516249-51a7-46d1-b19b-3c7e8acdffea',
          'mode': 1
        });
      }
      function unsubscribe() {
        _ajax('/api/payments/unsubscribe', 'POST')
      }

      function _ajax(url, method, body) {
        fetch(url, {
          credentials: 'same-origin',
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(body)
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json(); // レスポンスをJSONとして解析します
          })
          .then(data => {
            // データをここで使用します
            document.getElementsByClassName('messages')[0].innerHTML = JSON.stringify(data, null, 2);
            console.log(data);
          })
          .catch(error => {
            // エラーハンドリングを行います
            console.error('There has been a problem with your fetch operation:', error);
          });
      }
    </script>
    <button type="button" class="btn btn-primary" onclick="javascript:callgpt();">callgpt</button>
    <button type="button" class="btn btn-primary" onclick="javascript:callgpt3();">callgpt3</button>
    <button type="button" class="btn btn-primary" onclick="javascript:categories();">categories</button>
    <button type="button" class="btn btn-primary" onclick="javascript:prompts();">prompts</button>
    <button type="button" class="btn btn-primary" onclick="javascript:prompts1();">prompts1</button>
    <button type="button" class="btn btn-primary" onclick="javascript:prompts2();">prompts2</button>

    <button type="button" class="btn btn-primary" onclick="javascript:calldocsbot1();">docsbot1</button>
    <button type="button" class="btn btn-primary" onclick="javascript:calldocsbot2();">docsbot2</button>
    <button type="button" class="btn btn-primary" onclick="javascript:history1();">history1</button>
    <button type="button" class="btn btn-primary" onclick="javascript:history2();">history2</button>
    <br />
    <button type="button" class="btn btn-primary" onclick="javascript:error();">error</button>

    <button type="button" class="btn btn-primary" onclick="javascript:function_calling();">function</button>
    <button type="button" class="btn btn-primary" onclick="javascript:speed();">speed</button>

    <button type="button" class="btn btn-primary" onclick="javascript:check1();">check1</button>
    <button type="button" class="btn btn-primary" onclick="javascript:check2();">check2</button>

    <button type="button" class="btn btn-primary" onclick="javascript:hide();">hide</button>
    <button type="button" class="btn btn-primary" onclick="javascript:show();">show</button>
    <button type="button" class="btn btn-primary" onclick="javascript:unsubscribe();">unsubscribe</button>
    <!--
    <div class="menu">
      <h2>Menu</h2>
      <div class="menu-item" data-message="あなたは文章要約のプロです。あなたのゴールは、文章を要約し重要なポイントを箇条書きにまとめることです。このプロンプトに対し、まず「要約する文章を入力してください」という応答を返し、次に入力された文章を要約し、重要なポイントを抽出して箇条書きにしてください。">文章要約</div>
      <div class="menu-item" data-message="あなたは英語翻訳のプロです。あなたのゴールは、英文を日本語に翻訳し、さらに文章を要約して重要なポイントを箇条書きにまとめることです。
必ず次の１から３のプロセスを踏んでください
1. あなたの最初の応答は、「要約する英文を入力してください」と翻訳する文章が何かを聞いてください。
2. 英文を日本語に翻訳してください。最後に「文章を要約しますか？」と尋ねてください。
3. ２で翻訳した文章を要約し、さらに重要なポイントを箇条書きにしてください。">英文翻訳＋要約</div>
      <div class="menu-item" data-message="<instruction>

1. グラフタイプの入力: グラフタイプは、「bar」、「line」、「pie」、「doughnut」、「polarArea」、「radar」、「bubble」、「scatter」などが使えます。
2. データの入力: ユーザーがグラフに表示したいデータを次の形式でうけとる: labels: ['Label1', 'Label2', 'Label3'], data: [10, 20, 30]
Note:
  a. ユーザーにステップ1,2の内容を順に入力してもらうように依頼すること
  b. ステップ1, 2が完了後、3に進むこと
  c. 入力された内容や形式に不備があれば改善案を提示しつつ再入力を求めること

3. グラフのプレビュー生成:1,2のステップが完了してその入力されたグラフタイプとデータを元に、エンコード済みの
http://quickchart.io
のURLを作すること。その後、対応するグラフのプレビューを生成し、イメージリンクとソース引用を提供。下記のアウトプット形式で表示すること:
<output>
![Title of Image](link of image) [Source](link of source)">グラフ作成</div>
      <div class="menu-item" data-message=""><br /></div>
      <div class="menu-item" data-message="">プロンプト集</div>
      <div class="menu-item" data-message="">プロンプト作成</div>
      <div class="menu-item" data-message="">社内ナレッジ登録</div>
    </div>
-->
    <div class="chat">
      <div class="chat-window">
        <div class="loading-indicator" style="display: none;">
          <div class="spinner"></div>
        </div>
        <div class="mode-selector">
          <label for="chat-mode">チャットモード:</label>
          <select id="chat-mode">
            <option value="gigmon">ギグもんモード</option>
            <option value="chatgpt" selected="selected">ChatGPTモード</option>
          </select>
        </div>
        <div class="messages">
          <!-- メッセージはここに追加されます -->
        </div>
        <div class="input-area">
          <textarea id="input-message" class="input-message" rows="3" placeholder="メッセージを入力..."></textarea>
          <button class="send-message">Send</button>
        </div>
      </div>
    </div>
</div>
@endsection