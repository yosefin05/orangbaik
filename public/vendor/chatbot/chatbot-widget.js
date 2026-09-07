(function () {
    function initChatbotWidget(root) {
        if (!root) {
            return;
        }

        var config = JSON.parse(root.dataset.config || '{}');
        var endpoint = root.dataset.endpoint || '';
        var sessionKey = 'laravel_chatbot_session_id';
        var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        var open = false;
        var sessionId = window.localStorage.getItem(sessionKey) || '';
        var positionClass = (config.widget_position || 'bottom-right') === 'bottom-left' ? 'left' : 'right';

        root.innerHTML =
            '<div class="laravel-chatbot-widget ' + positionClass + '">' +
                '<button class="laravel-chatbot-toggle" type="button">Chat</button>' +
                '<div class="laravel-chatbot-panel">' +
                    '<div class="laravel-chatbot-header">' +
                        '<strong>' + escapeHtml(config.chatbot_name || 'Support Bot') + '</strong>' +
                        '<div class="laravel-chatbot-subtitle">' + escapeHtml(config.welcome_message || '') + '</div>' +
                    '</div>' +
                    '<div class="laravel-chatbot-messages"></div>' +
                    '<form class="laravel-chatbot-form">' +
                        '<input class="laravel-chatbot-input" type="text" placeholder="Type your message...">' +
                        '<button class="laravel-chatbot-submit" type="submit">Send</button>' +
                    '</form>' +
                '</div>' +
            '</div>';

        var widget = root.querySelector('.laravel-chatbot-widget');
        var toggle = root.querySelector('.laravel-chatbot-toggle');
        var panel = root.querySelector('.laravel-chatbot-panel');
        var header = root.querySelector('.laravel-chatbot-header');
        var form = root.querySelector('.laravel-chatbot-form');
        var input = root.querySelector('.laravel-chatbot-input');
        var submit = root.querySelector('.laravel-chatbot-submit');
        var messages = root.querySelector('.laravel-chatbot-messages');
        var primaryColor = config.primary_color || '#2563eb';

        toggle.style.background = primaryColor;
        header.style.background = primaryColor;
        submit.style.background = primaryColor;

        function addMessage(sender, text) {
            var row = document.createElement('div');
            row.className = 'laravel-chatbot-bubble-row ' + sender;

            var bubble = document.createElement('div');
            bubble.className = 'laravel-chatbot-bubble';
            bubble.textContent = text;
            bubble.style.background = sender === 'user' ? primaryColor : '#fff';
            bubble.style.color = sender === 'user' ? '#fff' : '#0f172a';
            bubble.style.border = sender === 'user' ? 'none' : '1px solid #dbe2ea';

            row.appendChild(bubble);
            messages.appendChild(row);
            messages.scrollTop = messages.scrollHeight;
        }

        toggle.addEventListener('click', function () {
            open = !open;
            panel.style.display = open ? 'block' : 'none';

            if (open && messages.childElementCount === 0) {
                addMessage('bot', config.welcome_message || 'Hello! How can I help you today?');
            }
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            var message = input.value.trim();

            if (!message) {
                return;
            }

            addMessage('user', message);
            input.value = '';
            addMessage('bot', 'Typing...');

            var response = await fetch(endpoint, {
                method: 'POST',
                headers: Object.assign({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }, csrf ? {'X-CSRF-TOKEN': csrf} : {}),
                body: JSON.stringify({message: message, session_id: sessionId})
            });

            var data = await response.json();
            sessionId = data.session_id || sessionId;

            if (sessionId) {
                window.localStorage.setItem(sessionKey, sessionId);
            }

            messages.lastChild?.remove();
            addMessage('bot', data.reply || config.fallback_message || 'Sorry, I could not understand your question.');
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-laravel-chatbot-widget]').forEach(initChatbotWidget);
    });
})();
