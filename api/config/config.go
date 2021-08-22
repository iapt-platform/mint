package config

import (
	"fmt"
	"os"

	"github.com/kelseyhightower/envconfig"
	"gopkg.in/yaml.v2"
)

type Config struct {
	Server struct {
		Port string `yaml:"port"`
		Host string `yaml:"host"`
	} `yaml:"server"`

	Database struct {
		Port     string `yaml:"port" envconfig:"DB_PORT"`
		Host     string `yaml:"host" envconfig:"DB_HOST"`
		Username string `yaml:"user" envconfig:"DB_USER"`
		Password string `yaml:"pass" envconfig:"DB_PASS"`
		SSLMode  string `yaml:"ssl" envconfig:"DB_SSL"`
	} `yaml:"database"`
}

func readFile(cfg *Config) {
	f, err := os.Open("config.yml")
	if err != nil {
		processError(err)
	}
	defer f.Close()

	decoder := yaml.NewDecoder(f)
	err = decoder.Decode(cfg)
	if err != nil {
		processError(err)
	}
}

func readEnv(cfg *Config) {
	err := envconfig.Process("", cfg)
	if err != nil {
		processError(err)
	}
}

func processError(err error) {
	fmt.Println(err)
	os.Exit(2)
}

func GetConfig() Config {
	var cfg Config
	// readFile(&cfg)
	readEnv(&cfg)
	return cfg
}

func main() {
	fmt.Printf("%+v", GetConfig())
}
