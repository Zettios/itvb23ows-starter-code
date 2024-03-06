pipeline {
    agent any

    stages {
	    stage('Setup') {
            steps {
                echo 'Setup fase'
            }
        }
        stage('Execute SonarQube scan') {
            agent {
                label '!windows'
            }
            steps {
                echo 'Executing SonarQube scan'
                script {
                    scannerHome = tool 'SonarQube Scanner'
                }
                withSonarQubeEnv('SonarQube_Hive') {
                    sh "${scannerHome}/bin/sonar-scanner -Dsonar.projectKey=sqp_95ec0aa97b17a116a718b1ce1f5240e2dc006d9e"
                }
            }
        }
        stage('Execute PHPUnit Tests') {
            steps {
                script {
                    docker.image('itvb23ows-starter-code-app:latest').inside {
                        sh 'php --version'
                        sh 'vendor/bin/phpunit src/.'
                    }
                }
            }
        }
    }
    post {
        always {
            sh "docker-compose down"
        }
        success {
            echo "Build successful"
        }
        failure {
            echo "Build failed, see console for the details"
        }
    }
}
