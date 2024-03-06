pipeline {
    agent { docker { image 'php:8.3.3-alpine3.19' } }
    stages {
        stage('build') {
            steps {
                sh 'php --version'
            }
        }
    }
}