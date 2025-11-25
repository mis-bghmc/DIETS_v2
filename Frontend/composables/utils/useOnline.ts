export function useOnline() {
  const isOnline = useState('isOnline', () => true)

  const update = () => {
    if (typeof navigator !== 'undefined') {
      isOnline.value = navigator.onLine
    }
  }

  if (typeof window !== 'undefined') {
    isOnline.value = navigator.onLine
    useEventListener(window, 'online', update)
    useEventListener(window, 'offline', update)
  }

  return { isOnline }
}
