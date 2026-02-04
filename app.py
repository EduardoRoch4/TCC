from flask import Flask
app = Flask(__name__)

@app.route("/")
def home():
    return "<h1>Bem-vindo ao meu TCC!</h1><p>Este é meu projeto rodando na nuvem.</p>"

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=80)
