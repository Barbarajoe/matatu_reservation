const jwt = require('jsonwebtoken');
const dotenv = require('dotenv');

dotenv.config();

const secretKey = process.env.JWT_SECRET || 'your_secret_key';

const tokenUtil = {
    generateToken: (userId) => {
        const payload = { id: userId };
        const options = { expiresIn: '1h' };
        return jwt.sign(payload, secretKey, options);
    },

    verifyToken: (token) => {
        try {
            return jwt.verify(token, secretKey);
        } catch (error) {
            return null;
        }
    }
};

module.exports = tokenUtil;