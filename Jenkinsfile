pipeline {
    agent any
    environment {
        PROJECT_ID = 'du01-android---wedding'
        CLUSTER_NAME = 'du01-test-deploy'
        LOCATION = 'us-central1-a'
        CREDENTIALS_ID = '2c23a60c-36cb-42e0-b94e-919ed740a19d'
        DOCKER_HUB_USERNAME = 'hungnv93'
        DOCKER_HUB_REPOSITORY_NAME = 'hello'
    }
    stages {
        stage("Checkout code") {
            steps {
                checkout scm
            }
        }
        stage("Build image") {
            steps {
                script {
                    // In thông tin biến môi trường để debug
                    echo "Building Docker image: ${env.DOCKER_HUB_USERNAME}/${env.DOCKER_HUB_REPOSITORY_NAME}:${env.BUILD_ID}"
                    
                    // Đảm bảo Docker đang chạy
                    sh 'docker version'

                    // Kiểm tra Dockerfile và nội dung thư mục hiện tại
                    sh 'ls -la'
                    sh 'cat Dockerfile'

                    // Xây dựng Docker image với nhật ký chi tiết
                    try {
                        myapp = docker.build("${env.DOCKER_HUB_USERNAME}/${env.DOCKER_HUB_REPOSITORY_NAME}:${env.BUILD_ID}")
                    } catch (Exception e) {
                        echo "Failed to build Docker image: ${e.getMessage()}"
                        error("Build failed")
                    }
                }
            }
        }
        stage("Push image") {
            steps {
                script {
                    docker.withRegistry('https://registry.hub.docker.com', 'dockerID') {
                        myapp.push("latest")
                        myapp.push("${env.BUILD_ID}")
                    }
                }
            }
        }        
        stage('Deploy to GKE') {
            steps {
                sh "sed -i 's/hello:latest/hello:${env.BUILD_ID}/g' deployment.yaml"
                step([$class: 'KubernetesEngineBuilder', projectId: env.PROJECT_ID, clusterName: env.CLUSTER_NAME, location: env.LOCATION, manifestPattern: 'deployment.yaml', credentialsId: env.CREDENTIALS_ID, verifyDeployments: true])
            }
        }

        // stage('Deploying App to Kubernetes') {
        //     steps {
        //         script {
        //         kubernetesDeploy(configs: "deployment.yml", kubeconfigId: "kubernetes")
        //         }
        //     }
        // }
    }    
}
