class AuthService {
    constructor(userModel, tokenUtil) {
        this.userModel = userModel;
        this.tokenUtil = tokenUtil;
    }

    async register(username, password) {
        const existingUser = await this.userModel.findOne({ username });
        if (existingUser) {
            throw new Error('User already exists');
        }

        const newUser = new this.userModel({ username, password });
        await newUser.save();
        return newUser;
    }

    async login(username, password) {
        const user = await this.userModel.findOne({ username });
        if (!user || !(await user.comparePassword(password))) {
            throw new Error('Invalid credentials');
        }

        const token = this.tokenUtil.generateToken(user._id);
        return { user, token };
    }

    async validateToken(token) {
        try {
            const decoded = this.tokenUtil.verifyToken(token);
            return decoded;
        } catch (error) {
            throw new Error('Invalid token');
        }
    }
}

export default AuthService;