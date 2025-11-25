export default defineEventHandler((event) => {
    const url = event.node.req.url || ""
    
    // Only intercept /api/*
    if (url.startsWith("/api/")) {
        const accept = getHeader(event, "accept") || ""
        
        // If it's a browser requesting HTML, redirect them
        if (accept.includes("text/html")) {
            return sendRedirect(event, "/", 302) // or "/login"
        }
    }
})