import { ChatbotPreviewPage } from '../../screens/public/ChatbotPreviewPage';

export function ChatbotPreviewRoutePage({ title, description }: { title: string; description: string }) {
  return <ChatbotPreviewPage title={title} description={description} />;
}
