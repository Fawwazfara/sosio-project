<template>
  <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden relative">
    <!-- Mobile Header -->
    <div class="px-4 pt-5 pb-2 md:hidden" v-if="!activeChat">
      <h1 class="text-2xl font-bold text-[#1C1917] tracking-tight">Pesan Internal</h1>
    </div>

    <main class="w-full max-w-6xl mx-auto md:px-6 lg:px-8 md:py-6 flex flex-1 h-full overflow-hidden">
      <div class="flex w-full h-full bg-[#FDFCF9] md:rounded-[18px] border border-transparent shadow-[var(--shadow-border)] overflow-hidden relative">

        <!-- Left: Contact List -->
        <div
          v-show="!activeChat || isDesktop"
          class="w-full md:w-[340px] lg:w-[380px] flex flex-col h-full bg-[#FDFCF9] border-r border-black/[0.07] shrink-0"
        >
          <div class="p-4 border-b border-black/[0.07]">
            <div class="relative">
              <UiIcon name="search" :size="18" :weight="500" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#78716C]" />
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Cari pesan atau nama guru..."
                class="ui-input pl-10"
              >
            </div>
          </div>

          <div class="flex-1 overflow-y-auto overscroll-contain divide-y divide-black/[0.05]">
            <button
              v-for="chat in filteredChatList"
              :key="chat.id"
              type="button"
              @click="openChat(chat)"
              :class="[
                'w-full text-left flex items-center gap-3.5 p-4 transition-[background-color,transform] duration-150 ease-out hover:bg-[#F7F4ED] active:scale-[0.99]',
                activeChat && activeChat.id === chat.id ? 'bg-[#F9EFE7]' : ''
              ]"
            >
              <div class="w-11 h-11 rounded-[11px] bg-[#292524] text-white flex items-center justify-center font-bold text-sm overflow-hidden shrink-0">
                <span v-if="!chat.avatar">{{ getInitials(chat.name) }}</span>
                <img v-else :src="chat.avatar" alt="Avatar" class="w-full h-full object-cover outline outline-1 -outline-offset-1 outline-black/10">
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-center gap-2">
                  <h2 class="text-sm font-bold text-[#1C1917] truncate">{{ chat.name }}</h2>
                  <span class="text-xs font-mono shrink-0 tabular-nums" :class="chat.unread > 0 ? 'text-[#A8551E] font-bold' : 'text-[#78716C]'">
                    {{ chat.lastTime }}
                  </span>
                </div>
                <div class="flex justify-between items-center gap-2 mt-1">
                  <p class="text-xs text-[#78716C] truncate">{{ chat.lastMessage }}</p>
                  <span v-if="chat.unread > 0" class="min-w-[20px] h-5 px-1.5 rounded-full bg-[#A8551E] text-white flex items-center justify-center text-xs font-bold tabular-nums shrink-0">
                    {{ chat.unread }}
                  </span>
                </div>
              </div>
            </button>
          </div>
        </div>

        <!-- Right: Active Thread -->
        <div
          v-if="activeChat || isDesktop"
          :class="[
            'flex-1 flex flex-col h-full bg-[#F5F3EE] relative',
            !activeChat && isDesktop ? 'items-center justify-center' : ''
          ]"
        >
          <div v-if="!activeChat && isDesktop" class="text-center p-8 max-w-sm ui-stagger ui-stagger-1">
            <div class="w-14 h-14 bg-[#FDFCF9] shadow-[var(--shadow-border)] rounded-[14px] flex items-center justify-center mx-auto mb-4 text-[#A8551E]">
              <UiIcon name="forum" :size="26" :weight="500" />
            </div>
            <h2 class="text-lg font-bold text-[#1C1917]">Pesan Internal Madrasah</h2>
            <p class="text-sm text-[#78716C] mt-1.5">Pilih salah satu wali kelas di sebelah kiri untuk membuka percakapan.</p>
          </div>

          <template v-else-if="activeChat">
            <!-- Chat Header -->
            <div class="h-[68px] px-4 sm:px-5 bg-[#FDFCF9] border-b border-black/[0.07] flex items-center gap-3 shrink-0">
              <button
                class="md:hidden min-h-[40px] min-w-[40px] -ml-1 text-[#1C1917] rounded-[10px] hover:bg-[#ECE8DF] flex items-center justify-center transition-[background-color,transform] duration-150 ease-out active:scale-[0.96]"
                aria-label="Kembali ke daftar kontak"
                @click="closeChat"
              >
                <UiIcon name="arrow_back" :size="19" :weight="600" directional />
              </button>

              <div class="w-10 h-10 rounded-[10px] bg-[#F9EFE7] shadow-[inset_0_0_0_1px_oklch(0.55_0.13_42_/_0.2)] text-[#7C3A10] font-bold text-xs flex items-center justify-center overflow-hidden shrink-0">
                <span v-if="!activeChat.avatar">{{ getInitials(activeChat.name) }}</span>
                <img v-else :src="activeChat.avatar" alt="Avatar" class="w-full h-full object-cover outline outline-1 -outline-offset-1 outline-black/10">
              </div>

              <div class="flex-1 min-w-0">
                <h2 class="text-sm font-bold text-[#1C1917] truncate">{{ activeChat.name }}</h2>
                <p class="text-xs text-[#78716C] truncate">Wali Kelas Terdaftar</p>
              </div>

              <button
                class="min-h-[40px] min-w-[40px] text-[#78716C] hover:text-[#1C1917] rounded-[10px] hover:bg-[#ECE8DF] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
                aria-label="Muat ulang percakapan"
                @click="fetchConversation(activeChat.id)"
              >
                <UiIcon name="sync" :size="18" :weight="600" :class="{ 'animate-spin': isFetchingChat }" />
              </button>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto overscroll-contain p-4 sm:p-5 flex flex-col gap-3">
              <div class="flex flex-col gap-3.5">
                <div
                  v-for="(msg, idx) in currentMessages"
                  :key="msg.id || idx"
                  :class="['flex', msg.isSender ? 'justify-end' : 'justify-start']"
                >
                  <div
                    :class="[
                      'max-w-[85%] md:max-w-[72%] rounded-[14px] px-4 py-3 text-sm',
                      msg.isSender
                        ? 'bg-[#292524] text-white shadow-[0_2px_6px_-1px_oklch(0.2_0.02_55_/_0.22)]'
                        : 'bg-[#FDFCF9] border border-transparent shadow-[var(--shadow-border)] text-[#1C1917]'
                    ]"
                  >
                    <p class="leading-relaxed">{{ msg.text }}</p>
                    <p
                      :class="[
                        'text-xs font-mono text-right mt-1.5 flex items-center justify-end gap-1 tabular-nums',
                        msg.isSender ? 'text-[#E7C0A1]' : 'text-[#78716C]'
                      ]"
                    >
                      <span>{{ msg.time }}</span>
                      <UiIcon v-if="msg.isSender" name="done" :size="13" :weight="600" />
                    </p>
                  </div>
                </div>
              </div>
              <div ref="chatEndRef"></div>
            </div>

            <!-- Input Footer -->
            <div class="p-3.5 bg-[#FDFCF9] border-t border-black/[0.07] shrink-0">
              <form @submit.prevent="sendMessage" class="flex items-center gap-2.5">
                <input
                  v-model="newMessage"
                  type="text"
                  placeholder="Ketik pesan untuk guru..."
                  class="ui-input flex-1"
                >
                <button
                  type="submit"
                  :disabled="!newMessage.trim() || isSending"
                  class="ui-btn-primary ui-btn-icon-l px-5 shrink-0"
                >
                  <UiIcon name="send" :size="17" :weight="600" directional />
                  <span class="hidden sm:inline">Kirim</span>
                </button>
              </form>
            </div>
          </template>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const windowWidth = ref(window.innerWidth);
const updateWidth = () => { windowWidth.value = window.innerWidth; };
const isDesktop = computed(() => windowWidth.value >= 768);

const chatList = ref([]);
const searchQuery = ref('');
const filteredChatList = computed(() => {
  if (!searchQuery.value) return chatList.value;
  return chatList.value.filter(c => c.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

let pollingInterval = null;
const fetchChatList = async () => {
  try {
    const res = await axios.get('/messages/chat-list');
    chatList.value = res.data;
  } catch (error) {
    console.error('Gagal mengambil chat list', error);
  }
};

const startPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
  pollingInterval = setInterval(() => {
    fetchChatList();
    if (activeChat.value) fetchConversation(activeChat.value.id, false);
  }, 10000);
};

const stopPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
};

onMounted(() => {
  window.addEventListener('resize', updateWidth);
  fetchChatList();
  startPolling();
});

onUnmounted(() => {
  window.removeEventListener('resize', updateWidth);
  stopPolling();
});

const activeChat = ref(null);
const newMessage = ref('');
const currentMessages = ref([]);
const chatEndRef = ref(null);
const isSending = ref(false);
const isFetchingChat = ref(false);

const getInitials = (name) => {
  if (!name) return 'U';
  const parts = name.split(' ').filter(n => !n.includes('(') && !n.includes(')'));
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.substring(0, 2).toUpperCase();
};

const openChat = async (chat) => {
  activeChat.value = chat;
  chat.unread = 0;
  await fetchConversation(chat.id, true);
};

const closeChat = () => { activeChat.value = null; };

const fetchConversation = async (userId, shouldScroll = false) => {
  isFetchingChat.value = true;
  try {
    const res = await axios.get(`/messages/conversation/${userId}`);
    currentMessages.value = res.data;
    if (shouldScroll) scrollToBottom();
  } catch (error) {
    console.error(error);
  } finally {
    isFetchingChat.value = false;
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (chatEndRef.value) chatEndRef.value.scrollIntoView({ behavior: 'smooth' });
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !activeChat.value) return;
  isSending.value = true;
  const msgText = newMessage.value.trim();
  newMessage.value = '';
  try {
    const res = await axios.post('/messages/send', {
      receiver_id: activeChat.value.id,
      message: msgText
    });
    currentMessages.value.push(res.data.data);
    const chatInList = chatList.value.find(c => c.id === activeChat.value.id);
    if (chatInList) {
      chatInList.lastMessage = res.data.data.text;
      chatInList.lastTime = res.data.data.time;
    }
    scrollToBottom();
  } catch (error) {
    console.error(error);
    notify('Gagal mengirim pesan.', 'error');
    newMessage.value = msgText;
  } finally {
    isSending.value = false;
  }
};
</script>
