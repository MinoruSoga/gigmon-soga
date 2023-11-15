<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Document</title>
    </head>
    <body>
        <h1>ChatBot</h1>
        {{-- <script type="text/javascript">
            var i = "ws://127.0.0.1:6001/chat", l = new WebSocket(i);

            l.onopen = function (e) {
                var o = { type: 'message', text: 'Hello, world!' };  // Test data
                l.send(JSON.stringify(o));
            };
            l.onerror = function (e) {
                console.error("WebSocket Error:", e);
            };
            l.onmessage = function (event) {
                console.log("Received from WebSocket:", event.data);
            };
        </script> --}}



        {{-- ============================== DocsBotをChatBotに名前変換 ============================== --}}
        {{-- <script type="text/javascript">
            (window.ChatBotAI = window.ChatBotAI || {}),
                (ChatBotAI.init = function (c) {
                    return new Promise(function (e, o) {
                        var t = document.createElement("script");
                        (t.type = "text/javascript"),
                            (t.async = !0),
                            (t.src =
                                "http://127.0.0.1:8000/api/get-chatbot-script");
                        var n = document.getElementsByTagName("script")[0];
                        n.parentNode.insertBefore(t, n),
                            t.addEventListener("load", function () {
                                window.ChatBotAI.mount({
                                    id: c.id,
                                    supportCallback: c.supportCallback,
                                    identify: c.identify,
                                    options: c.options,
                                    signature: c.signature,
                                });
                                var t;
                                (t = function (n) {
                                    return new Promise(function (e) {
                                        if (document.querySelector(n))
                                            return e(document.querySelector(n));
                                        var o = new MutationObserver(function (
                                            t
                                        ) {
                                            document.querySelector(n) &&
                                                (e(document.querySelector(n)),
                                                o.disconnect());
                                        });
                                        o.observe(document.body, {
                                            childList: !0,
                                            subtree: !0,
                                        });
                                    });
                                }),
                                    t && t("#chatbotai-root").then(e).catch(o);
                            }),
                            t.addEventListener("error", function (t) {
                                o(t.message);
                            });
                    });
                });
        </script>
        <script type="text/javascript">
            ChatBotAI.init({ id: "pvm7mtdki61Tq0Tc0jaO/FQXVtHigraERCEI1ge83" });
        </script> --}}

        {{-- ============================== 純正のDocsBot ============================== --}}
        {{-- <script>
            window.DocsBotAI = window.DocsBotAI || {};
            DocsBotAI.init = function(c) {
                return new Promise(function(e,o){
                    var t=document.createElement("script");
                    t.type="text/javascript";
                    t.async=!0;
                    t.src="https://widget.docsbot.ai/chat.js";
                    var n=document.getElementsByTagName("script")[0];
                    n.parentNode.insertBefore(t,n);
                    t.addEventListener("load",function(){
                        window.DocsBotAI.mount({
                            id:c.id,
                            supportCallback:c.supportCallback,
                            identify:c.identify,
                            options:c.options,
                            signature:c.signature
                        });
                        var t;
                        t=function(n){
                            return new Promise(function(e){
                                if(document.querySelector(n)) return e(document.querySelector(n));
                                var o=new MutationObserver(function(t){
                                    document.querySelector(n)&&(e(document.querySelector(n)),o.disconnect())
                                });
                                o.observe(document.body,{childList:!0,subtree:!0})
                            })
                        };
                        t&&t("#docsbotai-root").then(e).catch(o)
                    });
                    t.addEventListener("error",function(t){o(t.message)})
                });
            };
            DocsBotAI.init({id: "pvm7mtdki61Tq0Tc0jaO/FQXVtHigraERCEI1ge83"});
        </script> --}}
    </body>
</html>
