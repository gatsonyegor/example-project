export async function validateToken(token: string): Promise<Boolean> {
    try {
        const response = await fetch(
            `http://localhost/api/auth/validate-token`,
            {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            }
        );
        const data = await response.json();
        return data.error !== undefined && !data.error;
    } catch (error) {
        return false;
    }
}
