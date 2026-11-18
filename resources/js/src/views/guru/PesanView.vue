<template>
  <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden relative pb-20 md:pb-0">
    <main class="w-full max-w-4xl mx-auto md:px-6 lg:px-8 md:py-6 flex flex-1 h-full overflow-hidden">
      <div class="flex w-full h-full bg-[#FFFFFF] md:rounded-[18px] border border-transparent shadow-[var(--shadow-border)] overflow-hidden relative flex-col">

        <!-- Chat Header -->
        <div class="h-[68px] px-4 sm:px-5 bg-[#FFFFFF] border-b border-black/[0.07] flex items-center gap-3 shrink-0" v-if="adminProfile">
          <div class="w-10 h-10 rounded-[10px] bg-[#163A70] text-white flex items-center justify-center shrink-0">
            <UiIcon name="admin_panel_settings" :size="19" :weight="600" />
          </div>

          <div class="flex-1 min-w-0">
            <h1 class="text-sm sm:text-base font-bold text-[#0F172A] truncate">{{ adminProfile.name }} (Tata Usaha)</h1>
          </div>

          <button
            class="min-h-[40px] min-w-[40px] text-[#64748B] hover:text-[#0F172A] rounded-[10px] hover:bg-[#EAEFF6] flex items-center justify-center transition-[background-color,color,transform] duration-150 ease-out active:scale-[0.96]"
            aria-label="Muat ulang percakapan"
            @click="fetchConversation(adminProfile.id, false)"
          >
            <UiIcon name="sync" :size="18" :weight="600" :class="{ 'animate-spin': isFetchingChat }" />
          </button>
        </div>

        <div v-else class="h-[68px] flex items-center px-5 bg-[#FFFFFF] border-b border-black/[0.07] text-sm text-[#64748B]">
          Memuat profil Admin...
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto overscroll-contain p-4 sm:p-5 flex flex-col gap-3.5 bg-[#F2F4F8]">
          <div
            v-for="(msg, idx) in currentMessages"
            :key="msg.id || idx"
            :class="['flex', msg.isSender ? 'justify-end' : 'justify-start']"
          >
            <div
              :class="[
                'max-w-[85%] md:max-w-[72%] rounded-[14px] px-4 py-3 text-sm',
                msg.isSender
                  ? 'bg-[#163A70] text-white shadow-[0_2px_6px_-1px_oklch(0.2_0.02_55_/_0.22)]'
                  : 'bg-[#FFFFFF] border border-transparent shadow-[var(--shadow-border)] text-[#0F172A]'
              ]"
            >
              <p class="leading-relaxed">{{ msg.text }}</p>
              <p
                :class="[
                  'text-xs font-mono text-right mt-1.5 flex items-center justify-end gap-1 tabular-nums',
                  msg.isSender ? 'text-[#93C5FD]' : 'text-[#64748B]'
                ]"
              >
                <span>{{ msg.time }}</span>
                <UiIcon v-if="msg.isSender" name="done" :size="13" :weight="600" />
              </p>
            </div>
          </div>

          <div v-if="currentMessages.length === 0 && adminProfile && !isFetchingChat" class="text-center py-10 text-sm text-[#64748B]">
            Belum ada percakapan dengan Admin. Kirim pesan di bawah jika butuh koordinasi kelas.
          </div>

          <div ref="chatEndRef"></div>
        </div>

        <!-- Input Footer -->
        <div class="p-3.5 bg-[#FFFFFF] border-t border-black/[0.07] shrink-0">
          <form @submit.prevent="sendMessage" class="flex items-center gap-2.5">
            <input
              v-model="newMessage"
              type="text"
              placeholder="Ketik pesan untuk Admin..."
              :disabled="!adminProfile"
              class="ui-input flex-1"
            >
            <button
              type="submit"
              :disabled="!newMessage.trim() || !adminProfile || isSending"
              class="ui-btn-primary ui-btn-icon-l px-5 shrink-0"
            >
              <UiIcon name="send" :size="17" :weight="600" directional />
              <span class="hidden sm:inline">Kirim</span>
            </button>
          </form>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import { notify } from '@/utils/toast';

const adminProfile = ref(null);
const newMessage = ref('');
const currentMessages = ref([]);
const chatEndRef = ref(null);
const isSending = ref(false);
const isFetchingChat = ref(false);
let pollingInterval = null;

onMounted(async () => {
  await fetchAdminProfile();
  if (adminProfile.value) {
    await fetchConversation(adminProfile.value.id, true);
    startPolling();
  }
});

onUnmounted(() => { stopPolling(); });

const fetchAdminProfile = async () => {
  try {
    const res = await axios.get('/messages/admin-profile');
    adminProfile.value = res.data;
  } catch (error) {
    console.error('Gagal mendapatkan profile admin', error);
  }
};

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

const startPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
  pollingInterval = setInterval(() => {
    if (adminProfile.value) fetchConversation(adminProfile.value.id, false);
  }, 10000);
};

const stopPolling = () => {
  if (pollingInterval) clearInterval(pollingInterval);
};

const scrollToBottom = async () => {
  await nextTick();
  if (chatEndRef.value) chatEndRef.value.scrollIntoView({ behavior: 'smooth' });
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !adminProfile.value) return;
  isSending.value = true;
  const msgText = newMessage.value.trim();
  newMessage.value = '';
  try {
    const res = await axios.post('/messages/send', {
      receiver_id: adminProfile.value.id,
      message: msgText
    });
    currentMessages.value.push(res.data.data);
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
