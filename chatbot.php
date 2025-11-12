<!-- chatbot.php -->
<?php /* Chatbot converted from Chatbot.vue -> PHP/HTML/JS (no Gemini API) */ ?>

<!-- Chatbot Box -->
<div id="chatbot-box"
     class="fixed bottom-20 right-4 w-[90vw] sm:w-[380px] max-w-full bg-white border border-gray-200 rounded-2xl shadow-2xl z-[9998] max-h-[calc(100vh-140px)] hidden"
     style="font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;">
  <!-- Header -->
  <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-3 sm:p-4 rounded-t-2xl flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <div class="relative">
        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-full flex items-center justify-center shadow-lg overflow-hidden">
          <img src="assets/img/ChatBot-Girl-img.jpg" alt="JPMC Assistant" class="w-7 h-7 sm:w-9 sm:h-9 rounded-full object-cover" />
        </div>
        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
      </div>
      <div>
        <h3 class="font-semibold text-sm sm:text-base">JPMC Assistant</h3>
        <div id="status-text" class="text-[10px] sm:text-xs text-blue-100">Online</div>
      </div>
    </div>
    <button id="chatbot-close" class="text-white hover:text-gray-200 text-xl sm:text-2xl font-light">&times;</button>
  </div>

  <!-- Messages Container -->
  <div id="messages-container" class="h-44 sm:h-52 overflow-y-auto p-3 space-y-3 bg-gray-50">
    <!-- Messages will be injected here -->
  </div>

  <!-- Typing indicator placeholder - toggled via JS -->
  <!-- Quick Questions / Categories -->
  <div id="quick-questions" class="p-2 sm:p-3 border-t border-gray-100 bg-gray-50 overflow-x-auto scrollbar-visible">
    <!-- quick questions rendered here -->
  </div>

  <!-- Action Buttons -->
  <div class="p-2 sm:p-3 border-t border-gray-100 bg-white">
    <div class="flex space-x-1.5 sm:space-x-2">
      <button id="export-chat" class="flex-1 px-2 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[10px] sm:text-xs transition-all duration-200 flex items-center justify-center space-x-1">
        <i class="fas fa-download text-[9px] sm:text-[10px]"></i>
        <span class="hidden sm:inline">Export</span>
      </button>
      <button id="new-chat" class="flex-1 px-2 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-[10px] sm:text-xs transition-all duration-200 flex items-center justify-center space-x-1">
        <i class="fas fa-plus text-[9px] sm:text-[10px]"></i>
        <span>New</span>
      </button>
    </div>
  </div>

  <!-- Input -->
  <div class="p-2 sm:p-3 border-t border-gray-200">
    <div class="flex space-x-2">
      <!-- <input id="chat-input" type="text" placeholder="Type your question..." class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:border-blue-500" />
      <button id="chat-send" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm transition-all bg-gray-200 text-gray-400 cursor-not-allowed" disabled>
        <span class="hidden sm:inline">Send</span>
        <i class="fas fa-paper-plane sm:hidden"></i>
      </button> -->
    </div>
  </div>
</div>

<!-- Chatbot Toggle Button -->
<button id="chatbot-toggle" class="fixed bottom-4 right-4 z-[9999] bg-white rounded-full shadow-lg p-1.5 border border-gray-200 hover:bg-gray-100 transition-all hover:scale-105 group">
  <div class="relative">
    <img src="assets/img/ChatBot-Girl-img.jpg" alt="Chat" class="w-11 h-11 sm:w-12 sm:h-12 rounded-full object-cover" />
    <span id="unread-badge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold animate-pulse"></span>
    <span class="absolute inset-0 rounded-full bg-blue-400 opacity-0 group-hover:opacity-20 group-hover:animate-ping"></span>
  </div>
</button>

<style>
  /* scrollbar and misc styles copied from the Vue component */
  .scrollbar-visible {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f1f1;
  }
  .scrollbar-visible::-webkit-scrollbar { height: 6px; }
  .scrollbar-visible::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
  .scrollbar-visible::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
  .scrollbar-visible::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

  /* messages scrollbar */
  #messages-container::-webkit-scrollbar { width: 4px; }
  #messages-container::-webkit-scrollbar-track { background: #f1f1f1; }
  #messages-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
  #messages-container::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
  (function () {
    // State
    let isOpen = false;
    let messages = [];
    let conversationHistory = [];
    let conversationLevel = 1;
    let quickQuestions = [];
    let isTyping = false;
    let unreadCount = 0;

    // DOM refs
    const chatbotBox = document.getElementById('chatbot-box');
    const toggleBtn = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const messagesContainer = document.getElementById('messages-container');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send');
    const quickQuestionsEl = document.getElementById('quick-questions');
    const exportBtn = document.getElementById('export-chat');
    const newChatBtn = document.getElementById('new-chat');
    const unreadBadge = document.getElementById('unread-badge');

    // FAQ database (copied from Vue)
    const faqDatabase = {
        'General Information': [
            {
                question: 'What is injection molding?',
                answer: 'Injection molding is a manufacturing process where molten polymer material is injected into a mold cavity under high pressure. The material cools and solidifies, taking the shape of the mold cavity. This process is widely used for producing plastic parts in high volumes with excellent precision and repeatability.',
                keywords: ['injection', 'molding', 'define injection molding', 'process']
            },
            {
                question: 'How does injection molding work?',
                answer: 'The injection molding process consists of four main stages: 1) Clamping - The mold is securely closed, 2) Injection - Molten plastic is injected into the mold cavity, 3) Cooling - The plastic cools and solidifies, 4) Ejection - The finished part is ejected from the mold. This cycle repeats continuously for mass production.',
                keywords: ['how it works', 'process', 'stages', 'clamping', 'injection', 'cooling', 'ejection']
            },
            {
                question: 'What materials can be used in injection molding?',
                answer: 'Common materials include ABS, Polypropylene (PP), Polyethylene (PE), Polycarbonate (PC), Nylon (PA), and many others. Material selection depends on strength, temperature resistance, chemical resistance, and cost.',
                keywords: ['materials', 'plastics', 'ABS', 'polypropylene', 'polyethylene', 'polycarbonate', 'nylon']
            },
            {
                question: 'What are the advantages of injection molding?',
                answer: 'Advantages include: high production rates, excellent part consistency, complex geometries, minimal waste, ability to use multiple materials/colors, and cost-effectiveness for large runs. Also allows for great surface finish and dimensional accuracy.',
                keywords: ['advantages', 'benefits', 'pros', 'why use injection molding']
            },
            {
                question: 'What services does JPMC offer?',
                answer: 'JPMC offers:\n• Plastic injection molding\n• Rubber compression molding\n• Ultrasonic welding\n• Sub-assembly services\n• CNC machining\n• Mold design & fabrication\n• Silkscreen printing\n• 3D rapid prototyping',
                keywords: ['services', 'offer', 'plastic', 'rubber', 'welding', 'machining', 'sub-assembly', '3D prototyping']
            },
            {
                question: 'Where is JPMC located?',
                answer: 'James Polymers Manufacturing Corporation is located at:\n016 Panapaan 2, Bacoor City, 4102, Cavite, Philippines\nPhone: +63(2) 852989785\nEmail: jamespro_asia@yahoo.com',
                keywords: ['location', 'address', 'where', 'contact', 'phone', 'email']
            },
            {
                question: 'How long has JPMC been in business?',
                answer: 'JPMC has been in business since 1980, providing over 45 years of experience in polymer manufacturing.',
                keywords: ['experience', 'years', 'history', 'business']
            }
        ],

        'Contact & Facilities': [
            {
                question: 'What are your office hours?',
                answer: 'Our office hours are Monday to Friday, 8:00 AM to 5:00 PM (Philippine Time). We are closed on weekends and public holidays. For urgent inquiries outside office hours, please email us at jamespro_asia@yahoo.com and we will respond on the next business day.',
                keywords: ['office hours', 'working hours', 'business hours', 'open', 'schedule', 'time']
            },
            {
                question: 'Do you provide parking?',
                answer: 'Yes, we provide parking facilities for visitors at our Bacoor City location. Please inform us in advance of your visit so we can arrange parking space for you. For directions and parking instructions, contact us at +63(2) 852989785.',
                keywords: ['parking', 'visitor parking', 'parking space', 'park', 'vehicle']
            }
        ],

        'Technical Details': [
            {
                question: 'What is the typical cycle time for injection molding?',
                answer: 'Cycle times vary depending on part size, material, wall thickness, and complexity. Small parts: 10-30 seconds, Large parts: 1-5 minutes. Cooling time is usually the longest phase.',
                keywords: ['cycle time', 'duration', 'process time', 'speed']
            },
            {
                question: 'How do I calculate the cost of injection molding?',
                answer: 'Costs include tooling, material, machine time, labor, and overhead. Tooling is a significant upfront investment ($5,000-$100,000+) but becomes cost-effective with high production volumes.',
                keywords: ['cost', 'calculate', 'pricing', 'tooling', 'expense', 'budget']
            },
            {
                question: 'What are the key process parameters?',
                answer: 'Critical parameters: Melt temperature, Injection pressure (500-2,000 bar), Hold pressure and time, Cooling time and temperature, Mold temperature, and Injection speed.',
                keywords: ['parameters', 'melt temperature', 'injection pressure', 'cooling time', 'mold temperature', 'speed']
            },
            {
                question: 'How do I design parts for injection molding?',
                answer: 'Design considerations: uniform wall thickness (1-4mm), adequate draft angles (1-3°), proper gate placement, avoid sharp corners, design for easy ejection, consider material shrinkage.',
                keywords: ['design', 'parts', 'draft angles', 'gate', 'shrinkage', 'wall thickness']
            }
        ],

        'Quality & Standards': [
            {
                question: 'What are common injection molding defects?',
                answer: 'Common defects: Flow marks, Sink marks, Warping, Short shots, Flash, Voids, Burn marks. Each has specific causes and prevention methods.',
                keywords: ['defects', 'problems', 'issues', 'flow marks', 'sink marks', 'warping', 'short shots']
            },
            {
                question: 'How do I prevent injection molding defects?',
                answer: 'Prevention: proper material drying, optimal parameters, good mold design, maintenance, quality control, operator training. Specific defects require targeted solutions.',
                keywords: ['prevent', 'avoid', 'quality', 'defects', 'issues']
            },
            {
                question: 'What quality control measures are used?',
                answer: 'Quality control includes: process monitoring, part inspection, material testing, mold maintenance, statistical process control, final product testing.',
                keywords: ['quality', 'control', 'inspection', 'testing', 'ISO', 'standards']
            },
            {
                question: 'How do I maintain consistent part quality?',
                answer: 'Consistency is maintained through regular monitoring, consistent material handling, mold maintenance, operator training, quality checkpoints, and continuous improvement.',
                keywords: ['maintain', 'consistency', 'quality', 'control', 'standards']
            }
        ],

        'Process Optimization': [
            {
                question: 'How can I reduce cycle time?',
                answer: 'Optimize cooling system, use faster-cooling materials, reduce wall thickness, optimize gate placement, use hot runner systems, and perform regular equipment maintenance.',
                keywords: ['reduce', 'cycle time', 'optimize', 'cooling', 'materials', 'wall thickness', 'gate']
            },
            {
                question: 'What is the difference between hot and cold runner systems?',
                answer: 'Hot runners keep plastic molten in the mold, reducing waste and cycle time but increasing cost. Cold runners allow plastic to solidify in the runner, creating more waste but are cheaper.',
                keywords: ['hot runner', 'cold runner', 'difference', 'system', 'waste']
            },
            {
                question: 'How do I optimize material usage?',
                answer: 'Optimize material: proper gate design, use hot runner, minimal part design, recycle runner/sprue, proper material handling, process optimization to reduce defects.',
                keywords: ['material', 'usage', 'optimize', 'gate', 'recycle', 'waste']
            },
            {
                question: 'What maintenance is required for injection molding machines?',
                answer: 'Maintenance: daily cleaning/inspection, weekly lubrication, monthly hydraulic checks, quarterly electrical checks, annual comprehensive inspection, preventive maintenance per manufacturer.',
                keywords: ['maintenance', 'machine', 'cleaning', 'lubrication', 'inspection']
            }
        ],

        'Troubleshooting': [
            {
                question: 'What causes short shots in injection molding?',
                answer: 'Short shots occur when the mold cavity is not completely filled. Causes: low pressure, low material temperature, blocked gates, inadequate venting, contamination, mold design issues.',
                keywords: ['short shots', 'cause', 'pressure', 'temperature', 'venting', 'blocked gates', 'contamination']
            },
            {
                question: 'How do I fix warping issues?',
                answer: 'Warping is caused by uneven cooling and internal stresses. Fix: optimize cooling, adjust cooling time, improve part design, use low shrinkage materials, proper ejection.',
                keywords: ['warping', 'fix', 'solution', 'cooling', 'distortion']
            },
            {
                question: 'What causes sink marks and how do I prevent them?',
                answer: 'Sink marks are depressions from material shrinkage. Prevention: uniform wall thickness, proper gate placement, correct hold pressure/time, adequate cooling, low shrinkage materials.',
                keywords: ['sink marks', 'cause', 'prevention', 'shrinkage', 'wall thickness', 'gate']
            },
            {
                question: 'How do I resolve flow marks on parts?',
                answer: 'Flow marks are surface imperfections caused by material flow issues. Solutions: increase injection speed/pressure, optimize material temperature, improve gate design, ensure material drying, optimize mold temperature.',
                keywords: ['flow marks', 'surface', 'defects', 'solution', 'injection speed', 'temperature']
            }
        ],

        'Greetings': [
            {
                question: 'Hello',
                answer: 'Hello! How can I assist you with JPMC today?',
                keywords: ['hello', 'hi', 'greetings']
            },
            {
                question: 'Goodbye',
                answer: 'Goodbye! Feel free to reach out if you have more questions about JPMC.',
                keywords: ['goodbye', 'bye', 'see you']
            },
            {
                question: 'Thank you',
                answer: "You're welcome! I'm here to help with any questions about JPMC.",
                keywords: ['thank you', 'thanks', 'appreciate']
            },
            {
                question: 'Help',
                answer: 'Sure! You can ask me about JPMC services, technical details, quality standards, process optimization, and troubleshooting.',
                keywords: ['help', 'support', 'assist']
            }
        ]
    };



    // --- Utilities ---
    function formatTime(date) {
      const d = new Date(date);
      return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    function scrollToBottom() {
      setTimeout(() => {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
      }, 50);
    }

    function renderMessages() {
      messagesContainer.innerHTML = '';
      messages.forEach(msg => {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex transition-all duration-300 ' + (msg.isUser ? 'justify-end' : 'items-start space-x-2');

        if (!msg.isUser) {
          const avatarWrap = document.createElement('div');
          avatarWrap.className = 'w-6 h-6 sm:w-7 sm:h-7 bg-white rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm';
          const img = document.createElement('img');
          img.src = 'assets/img/ChatBot-Girl-img.jpg';
          img.alt = 'JPMC Assistant';
          img.className = 'w-5 h-5 sm:w-6 sm:h-6 rounded-full object-cover';
          avatarWrap.appendChild(img);
          wrapper.appendChild(avatarWrap);
        }

        const bubble = document.createElement('div');
        bubble.className = 'rounded-2xl p-2 sm:p-2.5 max-w-[75%] ' + (msg.isUser ? 'bg-blue-500 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-tl-sm');

        const p = document.createElement('p');
        p.className = 'text-[11px] sm:text-xs leading-relaxed whitespace-pre-line';
        p.textContent = msg.text;
        bubble.appendChild(p);

        if (msg.timestamp) {
          const t = document.createElement('p');
          t.className = 'text-[8px] sm:text-[9px] mt-1 ' + (msg.isUser ? 'text-blue-100 text-right' : 'text-gray-500');
          t.textContent = msg.timestamp;
          bubble.appendChild(t);
        }

        wrapper.appendChild(bubble);
        messagesContainer.appendChild(wrapper);
      });

      // typing indicator
      if (isTyping) {
        const typingWrap = document.createElement('div');
        typingWrap.className = 'flex items-start space-x-2';
        const avatarWrap = document.createElement('div');
        avatarWrap.className = 'w-6 h-6 sm:w-7 sm:h-7 bg-white rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm';
        const img = document.createElement('img');
        img.src = 'assets/img/ChatBot-Girl-img.jpg';
        img.alt = 'JPMC Assistant';
        img.className = 'w-5 h-5 sm:w-6 sm:h-6 rounded-full object-cover';
        avatarWrap.appendChild(img);

        const typingBubble = document.createElement('div');
        typingBubble.className = 'bg-gray-100 rounded-2xl rounded-tl-sm p-2';
        const dots = document.createElement('div');
        dots.className = 'flex space-x-1';
        ['0','0','0'].forEach((_, i) => {
          const d = document.createElement('div');
          d.className = 'w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce';
          if (i === 1) d.style.animationDelay = '0.1s';
          if (i === 2) d.style.animationDelay = '0.2s';
          dots.appendChild(d);
        });
        typingBubble.appendChild(dots);

        typingWrap.appendChild(avatarWrap);
        typingWrap.appendChild(typingBubble);
        messagesContainer.appendChild(typingWrap);
      }

      scrollToBottom();
    }

    // Render quick questions
    function showMainCategories() {
      quickQuestions = [
        { text: '📚 General Information', type: 'category', category: 'General Information' },
        { text: '📍 Contact & Facilities', type: 'category', category: 'Contact & Facilities' },
        { text: '🔧 Technical Details', type: 'category', category: 'Technical Details' },
        { text: '✅ Quality & Standards', type: 'category', category: 'Quality & Standards' },
        { text: '🔍 Process Optimization', type: 'category', category: 'Process Optimization' },
        { text: '⚠️ Troubleshooting', type: 'category', category: 'Troubleshooting' },
      ];
      renderQuickQuestions();
    }

    function getButtonClass(type) {
      const classes = {
        'category': 'bg-blue-100 hover:bg-blue-200 text-blue-800',
        'question': 'bg-purple-100 hover:bg-purple-200 text-purple-800',
        'followup': 'bg-green-100 hover:bg-green-200 text-green-800',
        'action': 'bg-gray-100 hover:bg-gray-200 text-gray-800'
      };
      return classes[type] || classes['action'];
    }

    function renderQuickQuestions() {
      quickQuestionsEl.innerHTML = '';
      if (!quickQuestions || quickQuestions.length === 0) {
        quickQuestionsEl.style.display = 'none';
        return;
      }
      quickQuestionsEl.style.display = 'block';
      const container = document.createElement('div');
      container.className = 'flex space-x-2 min-w-max pb-1';
      quickQuestions.forEach((q, idx) => {
        const btn = document.createElement('button');
        btn.className = 'px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs transition-colors whitespace-nowrap flex-shrink-0 ' + getButtonClass(q.type || 'action');
        btn.textContent = q.text;
        btn.onclick = () => handleQuickQuestion(q);
        container.appendChild(btn);
      });
      quickQuestionsEl.appendChild(container);
    }

    // Smart FAQ matching (simplified)
    function findBestMatch(userMessage) {
        const messageLower = userMessage.toLowerCase().trim();
        let bestMatch = null;
        let highestScore = 0;

        Object.entries(faqDatabase).forEach(([category, faqs]) => {
            faqs.forEach(faq => {
            let score = 0;

            // Match keywords
            if (faq.keywords && faq.keywords.length > 0) {
                faq.keywords.forEach(k => {
                if (messageLower.includes(k.toLowerCase())) {
                    score += 5; // weight for keyword match
                }
                });
            }

            // Match question directly
            if (messageLower === faq.question.toLowerCase()) score += 30;
            if (messageLower.includes(faq.question.toLowerCase()) || faq.question.toLowerCase().includes(messageLower)) score += 10;

            if (score > highestScore) {
                highestScore = score;
                bestMatch = Object.assign({}, faq, { category, score });
            }
            });
        });

        return highestScore > 0 ? bestMatch : null;
    }


    function addUserMessage(text) {
      messages.push({ text, isUser: true, timestamp: formatTime(new Date()) });
      conversationHistory.push({ question: text, timestamp: new Date(), level: conversationLevel });
      renderMessages();
    }

    function addBotMessage(text) {
      isTyping = true;
      renderMessages();
      setTimeout(() => {
        isTyping = false;
        messages.push({ text, isUser: false, timestamp: formatTime(new Date()) });
        if (!isOpen) {
          unreadCount++;
          updateUnreadBadge();
        }
        renderMessages();
      }, 700);
    }

    // Quick question handler
    function handleQuickQuestion(q) {
      if (q.type === 'category') {
        const faqs = faqDatabase[q.category] || [];
        addBotMessage(`Here are some questions about ${q.category}:`);
        quickQuestions = faqs.slice(0,5).map(f => ({ text: f.question, type: 'question', answer: f.answer, category: q.category }));
        renderQuickQuestions();
      } else if (q.type === 'question') {
        addUserMessage(q.text);
        setTimeout(() => {
          addBotMessage(q.answer);
          conversationLevel++;
          setTimeout(() => {
            quickQuestions = [
              { text: 'Ask another question', type: 'followup', category: q.category },
              { text: 'Back to categories', type: 'action' }
            ];
            renderQuickQuestions();
          }, 600);
        }, 600);
      } else if (q.type === 'followup') {
        if (q.category && faqDatabase[q.category]) {
          const categoryFaqs = faqDatabase[q.category];
          addBotMessage(`Here are more questions about ${q.category}:`);
          quickQuestions = categoryFaqs.slice(0,5).map(f => ({ text: f.question, type: 'question', answer: f.answer, category: q.category }));
          renderQuickQuestions();
        } else {
          addBotMessage("What would you like to know about?");
          showMainCategories();
        }
      } else if (q.text === 'Back to categories') {
        addBotMessage("Choose a category:");
        showMainCategories();
      } else {
        // fallback
        showMainCategories();
      }
    }

    // Send message
    async function sendMessage() {
      const message = chatInput.value.trim();
      if (!message) return;
      addUserMessage(message);
      chatInput.value = '';
      sendBtn.disabled = true;
      sendBtn.className = sendBtn.className.replace('bg-blue-600 text-white hover:bg-blue-700', 'bg-gray-200 text-gray-400 cursor-not-allowed');

      // Check FAQ
      const match = findBestMatch(message);
      if (match) {
        setTimeout(() => {
          addBotMessage(match.answer);
          conversationLevel++;
          setTimeout(() => {
            quickQuestions = [
              { text: 'Ask another question', type: 'followup', category: match.category },
              { text: 'Back to categories', type: 'action' }
            ];
            renderQuickQuestions();
          }, 500);
        }, 400);
      } else {
        // No Gemini API: fallback canned response + show categories
        isTyping = true;
        renderMessages();
        setTimeout(() => {
          isTyping = false;
          addBotMessage("I don't have a specific answer stored for that right now. I can help with general topics — choose a category below or try a different question.");
          showMainCategories();
        }, 900);
      }
    }

    // Export
    function exportChat() {
      const chatText = messages.map(msg => {
        const sender = msg.isUser ? 'You' : 'JPMC Assistant';
        return `[${msg.timestamp}] ${sender}: ${msg.text}`;
      }).join('\n\n');

      const blob = new Blob([chatText], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `jpmc-chat-${Date.now()}.txt`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      addBotMessage('✅ Chat exported!');
    }

    function startNewConversation() {
      conversationHistory = [];
      conversationLevel = 1;
      messages = [];
      renderMessages(); // Clear the display first
      initializeChatbot(); // Then reinitialize with welcome messages
    }

    function updateUnreadBadge() {
      if (unreadCount > 0 && !isOpen) {
        unreadBadge.textContent = unreadCount;
        unreadBadge.classList.remove('hidden');
      } else {
        unreadBadge.classList.add('hidden');
        unreadBadge.textContent = '';
      }
    }

    // Initialize
    function initializeChatbot() {
      messages = [
        { text: "Hello! I'm here to help with your questions about JPMC.", isUser: false, timestamp: formatTime(new Date()) },
        { text: "Ask me anything or choose from the quick questions below:", isUser: false, timestamp: formatTime(new Date()) }
      ];
      conversationHistory = [];
      conversationLevel = 1;
      showMainCategories();
      renderMessages();
      updateUnreadBadge();
    }

    // Toggle
    function toggleChatbot() {
      isOpen = !isOpen;
      if (isOpen) {
        chatbotBox.classList.remove('hidden');
        unreadCount = 0;
        updateUnreadBadge();
        // If empty messages, init
        if (messages.length === 0) initializeChatbot();
      } else {
        chatbotBox.classList.add('hidden');
      }
    }

    // Events
    toggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      toggleChatbot();
    });

    closeBtn.addEventListener('click', () => {
      toggleChatbot();
    });

    // Only add event listeners if the elements exist
    if (sendBtn) {
      sendBtn.addEventListener('click', () => {
        sendMessage();
      });
    }

    if (chatInput) {
      chatInput.addEventListener('input', () => {
        const trimmed = chatInput.value.trim();
        if (trimmed) {
          sendBtn.disabled = false;
          sendBtn.className = sendBtn.className.replace('bg-gray-200 text-gray-400 cursor-not-allowed', 'bg-blue-600 text-white hover:bg-blue-700');
        } else {
          sendBtn.disabled = true;
          sendBtn.className = sendBtn.className.replace('bg-blue-600 text-white hover:bg-blue-700', 'bg-gray-200 text-gray-400 cursor-not-allowed');
        }
      });

      chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          sendMessage();
        }
      });
    }

    exportBtn.addEventListener('click', exportChat);
    newChatBtn.addEventListener('click', startNewConversation);

    // Setup initial UI on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
      initializeChatbot();
    });

    // Expose for debugging (optional)
    window._JPMCChat = {
      open: () => { if (!isOpen) toggleChatbot(); },
      close: () => { if (isOpen) toggleChatbot(); }
    };
  })();
</script>
