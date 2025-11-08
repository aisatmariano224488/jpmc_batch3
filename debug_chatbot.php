<!-- debug_chatbot.php - Compact Debug version -->
<div id="chatbot-box" class="fixed bottom-16 right-4 w-56 bg-white border border-gray-300 rounded-lg shadow-lg hidden z-50">
  <div class="bg-blue-600 text-white p-1 rounded-t-lg flex justify-between items-center">
    <div class="flex items-center space-x-1">
      <h3 class="font-semibold text-xs">JPMC Assistant</h3>
      <div id="conversation-level" class="text-xs text-blue-100 bg-blue-700 px-1 rounded">L1</div>
    </div>
    <button id="close-chatbot" class="text-white hover:text-gray-200 text-sm">&times;</button>
  </div>

  <div id="chatbot-messages" class="h-32 overflow-y-auto p-1 space-y-1">
    <div class="text-center text-gray-600 text-xs">
      <p>Hello! Ask me anything or choose below:</p>
    </div>
  </div>

  <div id="faq-quick-questions" class="p-1 border-t border-gray-200 max-h-16 overflow-y-auto">
    <!-- Quick questions will be rendered here -->
  </div>

  <div class="p-1 border-t border-gray-200 bg-gray-50">
    <div class="grid grid-cols-4 gap-1">
      <button id="conversation-history-btn" class="px-1 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs transition-colors" title="History">
        📚
      </button>
      <button id="new-conversation-btn" class="px-1 py-1 bg-blue-200 hover:bg-blue-300 text-blue-700 rounded text-xs transition-colors" title="New Chat">
        🆕
      </button>
      <button id="end-conversation-btn" class="px-1 py-1 bg-orange-200 hover:bg-orange-300 text-orange-700 rounded text-xs transition-colors" title="End Conversation">
        🔚
      </button>
      <button id="debug-btn" class="px-1 py-1 bg-red-200 hover:bg-red-300 text-red-700 rounded text-xs transition-colors" title="Debug">
        🐛
      </button>
    </div>
  </div>

  <div class="p-1 border-t border-gray-200">
    <div class="flex space-x-1">
      <input type="text" id="chatbot-input" placeholder="Type question..." class="flex-1 px-1 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500">
      <button id="chatbot-send" class="px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 focus:outline-none">Send</button>
    </div>
  </div>
</div>

<a id="open-chatbot" href="#" class="fixed bottom-4 right-4 bg-blue-600 text-white p-1 rounded-full shadow-lg hover:bg-blue-700 z-40">
  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
  </svg>
</a>

<script>
  // Global variables to track conversation state
  let conversationHistory = [];
  let currentQuestionLevel = 0;
  let currentCategory = null;
  let debugMode = false;

  // Enhanced fetch FAQs with detailed error logging
  async function fetchFAQs() {
    console.log('🔍 Starting fetchFAQs()...');

    try {
      console.log('📡 Fetching from: get_faqs.php');
      const response = await fetch('get_faqs.php');
      console.log('📥 Response status:', response.status);
      console.log('📥 Response headers:', response.headers);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const text = await response.text();
      console.log('📄 Raw response text:', text);

      let data;
      try {
        data = JSON.parse(text);
        console.log('✅ JSON parsed successfully:', data);
      } catch (parseError) {
        console.error('❌ JSON parse error:', parseError);
        console.error('Raw text that failed to parse:', text);
        throw new Error('Failed to parse JSON response');
      }

      if (data.success) {
        console.log('✅ FAQ fetch successful, count:', data.count);
        return data.faqs || [];
      } else {
        console.error('❌ FAQ fetch failed:', data.error);
        return [];
      }

    } catch (error) {
      console.error('❌ Error in fetchFAQs:', error);
      console.error('Error details:', {
        name: error.name,
        message: error.message,
        stack: error.stack
      });
      return [];
    }
  }

  // Debug function to show database status
  async function debugDatabase() {
    console.log('🐛 Debug mode activated');

    try {
      const response = await fetch('test_db.php');
      const text = await response.text();
      console.log('Database test response:', text);

      // Show debug info in chatbot
      appendMessage('🐛 Database Debug Info:', false);
      appendMessage(text.substring(0, 300) + '...', false);

    } catch (error) {
      console.error('Debug error:', error);
      appendMessage('❌ Debug failed: ' + error.message, false);
    }
  }

  // Initialize FAQ structure with categories and follow-ups
  function initializeFAQStructure(faqs) {
    console.log('🏗️ Initializing FAQ structure with', faqs.length, 'FAQs');

    // Group FAQs by categories
    const categories = {
      'General Information': {
        questions: faqs.filter(faq =>
          faq.question.toLowerCase().includes('what') ||
          faq.question.toLowerCase().includes('how') ||
          faq.question.toLowerCase().includes('why')
        ).slice(0, 5),
        followUps: {
          'Process Details': [
            'Can you explain the process step by step?',
            'What are the key parameters?',
            'How long does it take?'
          ],
          'Benefits': [
            'What are the advantages?',
            'How does it compare to other methods?',
            'What makes it cost-effective?'
          ]
        }
      },
      'Technical Details': {
        questions: faqs.filter(faq =>
          faq.question.toLowerCase().includes('technical') ||
          faq.question.toLowerCase().includes('specification') ||
          faq.question.toLowerCase().includes('parameter')
        ).slice(0, 5),
        followUps: {
          'Implementation': [
            'How do I implement this?',
            'What equipment do I need?',
            'What are the requirements?'
          ],
          'Troubleshooting': [
            'What if something goes wrong?',
            'How do I fix common issues?',
            'What are the warning signs?'
          ]
        }
      },
      'Quality & Standards': {
        questions: faqs.filter(faq =>
          faq.question.toLowerCase().includes('quality') ||
          faq.question.toLowerCase().includes('standard') ||
          faq.question.toLowerCase().includes('certification')
        ).slice(0, 5),
        followUps: {
          'Certification Process': [
            'How do I get certified?',
            'What are the requirements?',
            'How long does certification take?'
          ],
          'Maintaining Standards': [
            'How do I maintain quality?',
            'What regular checks are needed?',
            'How often should I audit?'
          ]
        }
      }
    };

    // If no categories have questions, use all FAQs
    let hasQuestions = false;
    Object.values(categories).forEach(category => {
      if (category.questions.length > 0) hasQuestions = true;
    });

    if (!hasQuestions) {
      console.log('⚠️ No categorized questions found, using all FAQs');
      categories['All Questions'] = {
        questions: faqs.slice(0, 8),
        followUps: {
          'Learn More': [
            'Can you provide more details?',
            'What are the next steps?',
            'Where can I find additional information?'
          ]
        }
      };
    }

    console.log('🏗️ FAQ structure initialized:', categories);
    return categories;
  }

  function renderQuickQuestions(questions = null, category = null) {
    console.log('🎨 Rendering quick questions:', {
      questions,
      category
    });

    const container = document.getElementById('faq-quick-questions');
    container.innerHTML = '';

    if (questions) {
      // Show specific questions (follow-ups)
      questions.forEach(q => {
        const btn = document.createElement('button');
        btn.textContent = q;
        btn.className = 'faq-btn bg-green-100 hover:bg-green-200 text-green-800 px-2 py-1 rounded text-xs mb-1 mr-1 transition-colors';

        // Handle special conversation management options
        if (q === 'Continue conversation') {
          btn.onclick = () => continueConversation();
        } else if (q === 'Start new topic') {
          btn.onclick = () => {
            conversationHistory = [];
            currentQuestionLevel = 0;
            currentCategory = null;
            appendMessage('Starting a new topic. What would you like to know?', false);
            renderQuickQuestions();
          };
        } else if (q === 'View categories') {
          btn.onclick = () => renderQuickQuestions();
        } else {
          btn.onclick = () => sendUserMessage(q, true);
        }

        container.appendChild(btn);
      });
    } else {
      // Show main categories
      const categories = ['General Information', 'Technical Details', 'Quality & Standards'];
      categories.forEach(cat => {
        const btn = document.createElement('button');
        btn.textContent = cat;
        btn.className = 'faq-btn bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-1 rounded text-xs mb-1 mr-1 transition-colors';
        btn.onclick = () => showCategoryQuestions(cat);
        container.appendChild(btn);
      });
    }
  }

  function showCategoryQuestions(category) {
    console.log('📂 Showing questions for category:', category);

    currentCategory = category;
    currentQuestionLevel = 1;

    // Fetch and display questions for this category
    fetchFAQs().then(faqs => {
      console.log('📥 Fetched FAQs for category:', faqs.length);

      const categories = initializeFAQStructure(faqs);
      const categoryData = categories[category];

      if (categoryData && categoryData.questions.length > 0) {
        appendMessage(`Here are some questions about ${category}:`, false);

        const container = document.getElementById('faq-quick-questions');
        container.innerHTML = '';

        categoryData.questions.forEach(faq => {
          const btn = document.createElement('button');
          btn.textContent = faq.question;
          btn.className = 'faq-btn bg-purple-100 hover:bg-purple-200 text-purple-800 px-2 py-1 rounded text-xs mb-1 mr-1 transition-colors';
          btn.onclick = () => sendUserMessage(faq.question, false, faq.answer);
          container.appendChild(btn);
        });
      } else {
        appendMessage(`I don't have specific questions for ${category} yet. Try asking something general or choose another category.`, false);
        renderQuickQuestions();
      }
    }).catch(error => {
      console.error('❌ Error in showCategoryQuestions:', error);
      appendMessage('Sorry, there was an error loading questions for this category.', false);
    });
  }

  function appendMessage(text, isUser, isFollowUp = false) {
    const msgDiv = document.createElement('div');
    msgDiv.className = 'flex ' + (isUser ? 'justify-end' : 'justify-start');

    const bubble = document.createElement('div');
    bubble.className = (isUser ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800') + ' px-2 py-1 rounded text-xs max-w-xs';
    bubble.textContent = text;

    if (isFollowUp) {
      bubble.className += ' border-l-2 border-green-500';
    }

    msgDiv.appendChild(bubble);
    document.getElementById('chatbot-messages').appendChild(msgDiv);

    // Auto-scroll to bottom
    const messagesContainer = document.getElementById('chatbot-messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  function sendUserMessage(text, isFollowUp = false, directAnswer = null) {
    console.log('💬 User message:', text, {
      isFollowUp,
      directAnswer
    });

    appendMessage(text, true);

    // Add to conversation history
    conversationHistory.push({
      question: text,
      timestamp: new Date(),
      level: currentQuestionLevel
    });

    // Update conversation level display
    updateConversationLevel();

    setTimeout(async () => {
      if (directAnswer) {
        // Direct answer provided (from category questions)
        console.log('✅ Using direct answer');
        appendMessage(directAnswer, false);
        showFollowUpQuestions();
      } else {
        // Search for answer in FAQs
        console.log('🔍 Searching FAQs for answer...');
        const faqs = await fetchFAQs();
        console.log('📥 Found FAQs:', faqs.length);

        const faq = faqs.find(f =>
          f.question.toLowerCase().includes(text.toLowerCase()) ||
          text.toLowerCase().includes(f.question.toLowerCase())
        );

        if (faq) {
          console.log('✅ Found matching FAQ');
          appendMessage(faq.answer, false);
          showFollowUpQuestions();
        } else {
          console.log('❌ No matching FAQ found');
          appendMessage("I don't have a specific answer for that, but I can help guide you to relevant information. Try choosing a category or asking something more specific.", false);
          renderQuickQuestions();
        }
      }
    }, 500);
  }

  // Update conversation level display
  function updateConversationLevel() {
    const levelElement = document.getElementById('conversation-level');
    if (levelElement) {
      levelElement.textContent = `L${currentQuestionLevel}`;

      // Add visual indicator based on level
      if (currentQuestionLevel === 1) {
        levelElement.className = 'text-xs text-blue-100 bg-blue-700 px-1 rounded';
      } else if (currentQuestionLevel === 2) {
        levelElement.className = 'text-xs text-green-100 bg-green-700 px-1 rounded';
      } else if (currentQuestionLevel >= 3) {
        levelElement.className = 'text-xs text-purple-100 bg-purple-700 px-1 rounded';
      }
    }
  }

  function showFollowUpQuestions() {
    currentQuestionLevel++;

    // Get contextual follow-ups based on the last question
    let contextualFollowUps = [];
    if (conversationHistory.length > 0) {
      const lastQuestion = conversationHistory[conversationHistory.length - 1].question;
      contextualFollowUps = getContextualFollowUps(lastQuestion);
    }

    // Show contextual follow-ups if available, otherwise show general ones
    const followUps = contextualFollowUps.length > 0 ? contextualFollowUps : [
      'Can you provide more details?',
      'What are the practical implications?',
      'How does this apply to my situation?',
      'What are the next steps?'
    ];

    appendMessage('What would you like to know more about?', false);
    renderQuickQuestions(followUps);

    // Add conversation continuation options
    setTimeout(() => {
      appendMessage('Or you can start a new conversation by choosing a category:', false);
      renderQuickQuestions();
    }, 1000);
  }

  // Enhanced follow-up questions based on context
  function getContextualFollowUps(lastQuestion) {
    const question = lastQuestion.toLowerCase();

    if (question.includes('material') || question.includes('plastic') || question.includes('polymer')) {
      return [
        'What are the cost implications?',
        'How do I test material compatibility?',
        'What are the environmental considerations?',
        'Can you recommend alternatives?'
      ];
    } else if (question.includes('process') || question.includes('molding') || question.includes('injection')) {
      return [
        'What are the quality control steps?',
        'How do I optimize cycle time?',
        'What safety measures are needed?',
        'How do I maintain the equipment?'
      ];
    } else if (question.includes('quality') || question.includes('defect') || question.includes('problem')) {
      return [
        'How do I prevent this in the future?',
        'What are the root causes?',
        'How do I document the issue?',
        'When should I call for help?'
      ];
    } else if (question.includes('cost') || question.includes('price') || question.includes('budget')) {
      return [
        'What are the hidden costs?',
        'How do I calculate ROI?',
        'What financing options exist?',
        'How do I negotiate better rates?'
      ];
    } else {
      return [
        'Can you provide more details?',
        'What are the practical implications?',
        'How does this apply to my situation?',
        'What are the next steps?'
      ];
    }
  }

  // Show conversation history
  function showConversationHistory() {
    if (conversationHistory.length === 0) {
      appendMessage('No conversation history yet. Start by asking a question!', false);
      return;
    }

    appendMessage('Here\'s your conversation history:', false);
    conversationHistory.forEach((item, index) => {
      const time = new Date(item.timestamp).toLocaleTimeString();
      appendMessage(`${index + 1}. ${item.question} (${time})`, false);
    });

    appendMessage('You can continue from any point or start fresh. What would you like to do?', false);
    renderQuickQuestions(['Continue conversation', 'Start new topic', 'View categories']);
  }

  // Handle conversation continuation
  function continueConversation() {
    if (conversationHistory.length === 0) {
      appendMessage('No previous conversation to continue. Let\'s start fresh!', false);
      renderQuickQuestions();
      return;
    }

    const lastQuestion = conversationHistory[conversationHistory.length - 1];
    appendMessage(`Let\'s continue from: "${lastQuestion.question}"`, false);

    // Show relevant follow-up questions
    const relevantFollowUps = [
      'Can you elaborate on that?',
      'What are the practical implications?',
      'How does this apply to my situation?',
      'What are the next steps?'
    ];

    renderQuickQuestions(relevantFollowUps);
  }

  // Event listeners
  document.getElementById('open-chatbot').addEventListener('click', e => {
    e.preventDefault();
    document.getElementById('chatbot-box').classList.toggle('hidden');

    // Reset conversation when opening
    if (!document.getElementById('chatbot-box').classList.contains('hidden')) {
      conversationHistory = [];
      currentQuestionLevel = 0;
      currentCategory = null;
      renderQuickQuestions();
    }
  });

  // Conversation history button
  document.addEventListener('DOMContentLoaded', () => {
    const historyBtn = document.getElementById('conversation-history-btn');
    const newChatBtn = document.getElementById('new-conversation-btn');
    const endConversationBtn = document.getElementById('end-conversation-btn');
    const debugBtn = document.getElementById('debug-btn');

    if (historyBtn) {
      historyBtn.addEventListener('click', showConversationHistory);
    }

    if (newChatBtn) {
      newChatBtn.addEventListener('click', () => {
        conversationHistory = [];
        currentQuestionLevel = 0;
        currentCategory = null;
        appendMessage('Starting a new conversation. What would you like to know?', false);
        renderQuickQuestions();
      });
    }

    if (endConversationBtn) {
      endConversationBtn.addEventListener('click', () => {
        conversationHistory = [];
        currentQuestionLevel = 0;
        currentCategory = null;
        appendMessage('Conversation ended. Returning to main categories.', false);
        renderQuickQuestions();
      });
    }

    if (debugBtn) {
      debugBtn.addEventListener('click', debugDatabase);
    }
  });

  document.getElementById('close-chatbot').onclick = () => {
    document.getElementById('chatbot-box').classList.add('hidden');
  };

  document.getElementById('chatbot-send').onclick = () => {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    if (message) {
      sendUserMessage(message);
      input.value = '';
    }
  };

  document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      document.getElementById('chatbot-send').click();
    }
  });

  // Initialize chatbot
  document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Chatbot initializing...');
    renderQuickQuestions();

    // Test database connection on startup
    setTimeout(async () => {
      console.log('🔍 Testing database connection on startup...');
      const faqs = await fetchFAQs();
      console.log('📊 Startup FAQ count:', faqs.length);

      if (faqs.length === 0) {
        console.warn('⚠️ No FAQs found on startup');
        appendMessage('⚠️ Warning: No FAQs found in database. Please check database connection.', false);
      } else {
        console.log('✅ Database connection working, found', faqs.length, 'FAQs');
      }
    }, 1000);
  });
</script>