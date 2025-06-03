self.onmessage = async (e) => {
    try {
        let { id, image } = e.data;
        const response = await fetch(image);
        if (!response.ok) {
            throw new Error(`Failed to fetch image: ${response.statusText}`);
        }
        const blob = await response.blob();
        const bitmap = await createImageBitmap(blob);
        self.postMessage({ id, bitmap });
    } catch (error) {
        self.postMessage({ id, error: error.message });
    }
};