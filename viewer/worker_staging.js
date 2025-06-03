const cache = new Map();
const CACHE_EXPIRATION_TIME = 3600 * 1000;
onmessage = async function (e) {
    let { baseImageSrc, overlayImageSrc, id } = e.data;
    const cacheKey = "staging_b64_" + id;
    if (cache.has(cacheKey)) {
        const cachedData = cache.get(cacheKey);
        if (cachedData && Date.now() - cachedData.timestamp < CACHE_EXPIRATION_TIME) {
            postMessage({ base64Image: cachedData.base64Image, index_array: id });
            return;
        }
    }
    try {
        let baseBitmap, overlayBitmap;
        if (baseImageSrc instanceof ImageBitmap) {
            baseBitmap = baseImageSrc;
        } else {
            const baseResponse = await fetch(baseImageSrc);
            if (!baseResponse.ok) throw new Error("Failed to fetch base image.");
            const baseBlob = await baseResponse.blob();
            baseBitmap = await createImageBitmap(baseBlob);
        }
        if (overlayImageSrc instanceof ImageBitmap) {
            overlayBitmap = overlayImageSrc;
        } else {
            const overlayResponse = await fetch(overlayImageSrc);
            if (!overlayResponse.ok) throw new Error("Failed to fetch overlay image.");
            const overlayBlob = await overlayResponse.blob();
            overlayBitmap = await createImageBitmap(overlayBlob);
        }
        const canvas = new OffscreenCanvas(baseBitmap.width, baseBitmap.height);
        const ctx = canvas.getContext("2d");
        ctx.drawImage(baseBitmap, 0, 0);
        ctx.drawImage(overlayBitmap, 0, 0, baseBitmap.width, baseBitmap.height);
        const outputBlob = await canvas.convertToBlob({ type: "image/jpeg", quality: 1 });
        const reader = new FileReader();
        reader.onloadend = () => {
            const base64Image = reader.result;
            cache.set(cacheKey, {
                base64Image: base64Image,
                timestamp: Date.now()
            });
            postMessage({ base64Image: base64Image, index_array: id });
        };
        reader.readAsDataURL(outputBlob);
    } catch (error) {
        console.error(`Error processing message ID ${id}:`, error);
        postMessage({ error: error.message });
    }
};