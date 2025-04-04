class AuthController {
    constructor(authService) {
        this.authService = authService;
    }

    async register(req, res) {
        try {
            const user = await this.authService.register(req.body);
            res.status(201).json({ message: "User registered successfully", user });
        } catch (error) {
            res.status(400).json({ message: error.message });
        }
    }

    async login(req, res) {
        try {
            const { username, password } = req.body;
            const token = await this.authService.login(username, password);
            res.status(200).json({ message: "Login successful", token });
        } catch (error) {
            res.status(401).json({ message: error.message });
        }
    }
}

export default AuthController;